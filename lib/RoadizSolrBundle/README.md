# Roadiz Solr Search engine bundle

![Run test status](https://github.com/roadiz/solr-bundle/actions/workflows/run-test.yml/badge.svg?branch=develop)

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require roadiz/solr-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require roadiz/solr-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\SolrBundle\RoadizSolrBundle::class => ['all' => true],
];
```

## Configuration

### Docker compose

Here is an example using docker compose to run Solr Cloud in your project:

```yaml
services:
    # ...
    solr:
        image: solr:9-slim
        volumes:
            - solr:/var/solr
        environment:
            ZK_HOST: "zookeeper:2181"
        depends_on: [ zookeeper ]

    zookeeper:
        image: pravega/zookeeper:0.2.15
        volumes:
            - zookeeper-data:/data
            - zookeeper-datalog:/datalog
            - zookeeper-logs:/logs
        environment:
            ZOO_4LW_COMMANDS_WHITELIST: mntr,conf,ruok

volumes:
    # ...
    solr:
    zookeeper-data:
    zookeeper-datalog:
    zookeeper-logs:
```

### DotEnv variables

```dotenv
###> nelmio/solarium-bundle ###
SOLR_HOST=solr
SOLR_PORT=8983
SOLR_PATH=/
SOLR_CORE_NAME=roadiz
# For Solr Cloud, use the collection name instead of core name
SOLR_COLLECTION_NAME=roadiz
SOLR_COLLECTION_NUM_SHARDS=1
SOLR_COLLECTION_REPLICATION_FACTOR=1
SOLR_SECURE=0
###< nelmio/solarium-bundle ###
```

### Solarium config

Update `nelmio/solarium-bundle` default **config**
```yaml
# config/packages/nelmio_solarium.yaml
nelmio_solarium:
    endpoints:
        default:
            # We use Solr Cloud with collection
            host: '%env(SOLR_HOST)%'
            port: '%env(int:SOLR_PORT)%'
            path: '%env(SOLR_PATH)%'
            core: '%env(SOLR_CORE_NAME)%'
            #core: '%env(SOLR_COLLECTION_NAME)%'
    clients:
        default:
            endpoints: [default]
            # You can customize the http timeout (in seconds) here. The default is 5sec.
            adapter_timeout: 5
```

Configure fuzzy search options in a dedicated `roadiz_solr` config file:
```yaml
# config/packages/roadiz_solr.yaml
roadiz_solr:
    search:
        fuzzy_proximity: 2
        fuzzy_min_term_length: 3
```

You can use Solr Cloud with a collection instead of a core by setting the `SOLR_COLLECTION_NAME` environment variable and commenting the `core` line.
Then you will need to set the `SOLR_COLLECTION_NUM_SHARDS` and `SOLR_COLLECTION_REPLICATION_FACTOR` variables to configure your collection and execute
`solr:init` command to create the collection.

Fuzzy search options should now be configured in `roadiz_solr.search`.
For backward compatibility, `roadiz_core.solr.search` is still read as a fallback during migration.

#### Extending Solr configuration

If you want to add/remove fields and update filters you can add an event-subscriber to the `RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent` event.
An abstract subscriber is provided in the bundle to provide helper methods to add fields and filters: `RZ\Roadiz\SolrBundle\EventListener\AbstractSolrInitializationSubscriber`.

### Initialize Solr Core or Collection

```shell
# Initialize Solr collection (for Solr Cloud)
bin/console solr:init

# Reindex all NodesSources
bin/console solr:reindex
```

### Drop Solr Collection

```shell
bin/console solr:drop
```

### Api Resources

Expose the `NodesSourcesSearchController` at `/api/search` by declaring the
`SearchResultItem` resource. It is a **virtual, read-only** resource: it has no
identifier and no item operation, so every result is serialized with a unique
skolem IRI (`/.well-known/genid/...`) and the matched entity is nested under the
`item` property, alongside its `highlighting`.

```yaml
# config/api_resources/search.yml
resources:
    RZ\Roadiz\SolrBundle\SearchResultItem:
        shortName: SearchResultItem
        description: 'A single Solr search result, wrapping a matched resource and its highlighting.'
        types:
            - SearchResultItem
        # Virtual, read-only resource: no identifier and no item operation, so each
        # member is serialized with a unique skolem IRI (`/.well-known/genid/...`).
        stateless: true
        operations:
            search_collection:
                class: ApiPlatform\Metadata\GetCollection
                method: 'GET'
                uriTemplate: '/search'
                controller: RZ\Roadiz\SolrBundle\Controller\NodesSourcesSearchController
                read: false
                normalizationContext:
                    groups:
                        - get
                        - nodes_sources_base
                        - nodes_sources_default
                        - urls
                        - tag_base
                        - translation_base
                        - document_display
                openapi:
                    summary: Search NodesSources resources
                    description: |
                        Search all website NodesSources resources using **Solr** full-text search engine
                    tags:
                        - Search
                    parameters:
                        -   type: string
                            name: search
                            in: query
                            required: true
                            description: Search pattern
                            schema:
                                type: string
                        -   name: tag_name
                            in: query
                            required: false
                            description: |
                                Filter search results on one or more visible tag names (matches the
                                `facet_tags_ss` facet). Repeat the param to filter on several tags.
                            schema:
                                type: array
                                items:
                                    type: string
                            style: form
                            explode: true
                        -   name: node_type
                            in: query
                            required: false
                            description: |
                                Filter search results on one or more node types (matches the
                                `node_type_s` facet). Only node types within the endpoint allowlist
                                are returned. Repeat the param to filter on several node types.
                            schema:
                                type: array
                                items:
                                    type: string
                            style: form
                            explode: true
```

> **Migrating from `/nodes_sources/search`:** the operation moved from the
> `NodesSources` resource (`/api/nodes_sources/search`) to a dedicated
> `SearchResultItem` resource at `/api/search`. Update your front-end calls and
> read the matched entity from the `item` property of each `hydra:member`.

#### Content visibility

By default the endpoint only returns **published** content: the handler applies
`node_status_i:PUBLISHED` and `published_at_dt:[* TO NOW/MINUTE]`, hiding drafts,
pending and not-yet-published (embargoed) content. With a valid preview token,
`NodesSourcesSearchController::getCriteria()` widens the query to
`status <= PUBLISHED` so previewers can see **draft, pending and published**
content, including embargoed items. The `NOW/MINUTE` rounding lets consecutive
requests share Solr's filter cache.

#### Faceted search

Responses embed a `facets` object next to `hydra:member`. `NodeSourceSearchFacetSubscriber`
registers JSON facet terms for `node_type` (`node_type_s`), `document_type`
(`document_type_s`) and `tag_name` (`facet_tags_ss`). Facets are exposed through
`FacetedSearchResultsInterface::getFacets()` and appended to the Hydra collection
by the `FacetedCollectionNormalizer` decorator. The tag facet fields
(`facet_tags_ss` and `facet_tags_slugs_ss`), populated by
`DefaultNodesSourcesIndexingSubscriber`, only contain **visible** tags.

> **Note:** the `tag_name` facet exposes **translated tag names**, not slugs. A
> Solr facet bucket is a flat `{ val, count }` pair and cannot hold a label *and*
> a value, so the displayed name is also the value you filter with. The frontend
> can render facets as-is and echo the selected value back into `?tag_name=...`,
> but that value is localized (and therefore not a stable, language-neutral
> identifier). `facet_tags_slugs_ss` is indexed for slug-based queries, not to
> pair slugs with names inside a facet response.

#### Filtering search results

Optional filter params are handled by subscribers listening to
`NodeSourceSearchQueryEvent`:

- **`tag_name`** — `NodeSourceSearchTagsFilterSubscriber` (`facet_tags_ss`).
- **`node_type`** — `NodeSourceSearchNodeTypeFilterSubscriber` (`node_type_s`).

Each accepts a single value (`?node_type=Page`) or several
(`?node_type[]=Page&node_type[]=Article`) and escapes values with
`Solarium\Core\Query\Helper::escapePhrase()`. Because Solr ANDs filter queries,
`node_type` can only narrow within the controller's node-type allowlist. Add your
own filters by subscribing to `NodeSourceSearchQueryEvent` and appending filter
queries (or facets) on the Solarium query.

### Monolog

Add a solr handler to your monolog config if you want to separate its logs in a different file.
```yaml
# config/packages/monolog.yaml
monolog:
    handlers:
        solr:
            type: stream
            path: "%kernel.logs_dir%/solr.%kernel.environment%.log"
            level: debug
            channels: ["searchEngine"]
```

### Cron

This bundle provides a new `CronTask` to update Solr index each night at 3:30 AM:

```php
#[AsCronTask(
    expression: '30 3 * * *',
    jitter: 120,
    arguments: '--no-debug -n -q',
)]
```

Make sure to run Symfony scheduler.

## Contributing

Report [issues](https://github.com/roadiz/core-bundle-dev-app/issues) and send [Pull Requests](https://github.com/roadiz/core-bundle-dev-app/pulls) in the [main Roadiz repository](https://github.com/roadiz/core-bundle-dev-app)
