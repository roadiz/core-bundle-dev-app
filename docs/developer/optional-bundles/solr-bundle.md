# Solr Bundle

The Roadiz Solr Bundle provides full-text search engine integration using Apache Solr. It allows you to index and search NodesSources and Documents with advanced search capabilities.

::: info
**Since version 2.6**, Roadiz moved Apache Solr from its Core bundle to a separate optional bundle. It supports both _Solr standalone core_ and _SolrCloud mode_ with collections.
:::

## Installation

Install the bundle using Composer:

```bash
composer require roadiz/solr-bundle
```

If you're not using Symfony Flex, you'll need to manually enable the bundle in `config/bundles.php`:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\SolrBundle\RoadizSolrBundle::class => ['all' => true],
];
```

## Configuration

### Environment Variables

Add the following variables to your `.env` or `.env.local` file:

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

### Solarium Configuration

Configure the Solarium client in `config/packages/nelmio_solarium.yaml`:

```yaml
# config/packages/nelmio_solarium.yaml
nelmio_solarium:
    endpoints:
        default:
            host: '%env(SOLR_HOST)%'
            port: '%env(int:SOLR_PORT)%'
            path: '%env(SOLR_PATH)%'
            # Use core for standalone Solr
            core: '%env(SOLR_CORE_NAME)%'
            # Or use collection for SolrCloud
            #core: '%env(SOLR_COLLECTION_NAME)%'
    clients:
        default:
            endpoints: [default]
            # You can customize the http timeout (in seconds) here. The default is 5sec.
            adapter_timeout: 5
```

Configure fuzzy search options in `config/packages/roadiz_solr.yaml`:

```yaml
# config/packages/roadiz_solr.yaml
roadiz_solr:
    search:
        fuzzy_proximity: 2
        fuzzy_min_term_length: 3
```

::: tip
You can use Solr in 2 ways:
- **Standalone core**: Set the `core` parameter to `SOLR_CORE_NAME`
- **SolrCloud collection**: Set the `core` parameter to `SOLR_COLLECTION_NAME` and configure shards/replication
:::

Fuzzy search options are configured in `roadiz_solr.search`.
For backward compatibility, `roadiz_core.solr.search` is still read as a fallback during migration.

### API Platform Integration

Expose the full-text search endpoint by declaring the `SearchResultItem` resource.
It is a **virtual, read-only** API resource: it has no identifier and no item
operation, so API Platform serializes every result with a unique
[skolem IRI](https://api-platform.com/docs/core/serialization/#embedding-the-json-ld-context)
(`/.well-known/genid/...`) instead of reusing the same `@id`. The operation is
served at `/api/search` and wraps each matched resource together with its Solr
highlighting.

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

::: warning Migrating from `/nodes_sources/search`
Previous versions exposed the search operation on the `NodesSources` resource at
`/api/nodes_sources/search`. It is now a dedicated `SearchResultItem` resource at
`/api/search`. Update your front-end calls accordingly, and note that each
`hydra:member` now carries a skolem `@id` instead of a `NodesSources` IRI — the
matched entity is nested under the `item` property, next to `highlighting`.
:::

#### Content visibility

The search endpoint only returns **published** content by default. The handler
automatically applies `node_status_i:PUBLISHED` and
`published_at_dt:[* TO NOW/MINUTE]`, so drafts, pending and not-yet-published
(embargoed) content stay hidden. When a valid **preview** token is present
(see [`PreviewResolverInterface`](../../developer/security/security.md)),
`NodesSourcesSearchController::getCriteria()` widens the query to
`status <= PUBLISHED`, letting previewers see **draft, pending and published**
content, including embargoed items.

::: tip
The temporal filter is rounded with `NOW/MINUTE` (instead of an exact
per-request timestamp) so that consecutive requests share the same filter query
string and benefit from Solr's filter cache.
:::

### Faceted search

Search responses embed a `facets` object alongside `hydra:member`, so you can
build faceted navigation UIs without a second request. Facets are computed over
the current query domain (`q` + filter queries), so counts always reflect the
published/visible documents actually returned.

```json
{
    "@context": "/api/contexts/SearchResultItem",
    "@id": "/api/search",
    "@type": "hydra:Collection",
    "hydra:totalItems": 31,
    "hydra:member": [
        {
            "@id": "/.well-known/genid/xxxxxxxx",
            "@type": "SearchResultItem",
            "item": { "@id": "/api/pages/2222", "@type": "Page", "title": "…" },
            "highlighting": { "title_txt_en": ["<span class=\"solr-highlight\">Test</span> page"] }
        }
    ],
    "facets": {
        "node_type": { "buckets": [ { "val": "Page", "count": 18 } ] },
        "document_type": { "buckets": [ { "val": "NodesSources", "count": 31 } ] },
        "tag_name": { "buckets": [ { "val": "Category 1 EN", "count": 3 } ] }
    }
}
```

Under the hood:

- **`NodeSourceSearchFacetSubscriber`** subscribes to `NodeSourceSearchQueryEvent`
  and registers JSON facet terms for `node_type` (`node_type_s`), `document_type`
  (`document_type_s`) and `tag_name` (`facet_tags_ss`).
- Facets are exposed to the API through `FacetedSearchResultsInterface`. Any
  search result set implementing it (such as `SolrSearchResults`) returns its
  facets via `getFacets()`, and the `FacetedCollectionNormalizer` decorator
  appends them to the Hydra collection output.
- Two dedicated Solr fields back the tag facet and are populated by
  `DefaultNodesSourcesIndexingSubscriber`: `facet_tags_ss` (localized tag names)
  and `facet_tags_slugs_ss` (tag slugs). Both only contain **visible** tags, so
  hidden/technical tags never appear in facets.

#### Why the tag facet exposes translated names, not slugs

The `tag_name` facet is built on `facet_tags_ss`, which holds the **translated
tag names** for the document's locale — not the tag slugs. This is a deliberate
tradeoff imposed by Solr's faceting model.

A Solr JSON facet bucket is a flat `{ "val": <field value>, "count": <n> }`
pair. There is **no way to attach metadata to a bucket** — you cannot store a
translated name as the *label* and a slug as the *value*. Each bucket carries a
single string, and that same string is what a client must send back to filter on
it. So you have to pick one:

- **Facet on translated names** (what Roadiz does): buckets are display-ready and
  the `tag_name` filter round-trips the exact string shown in the UI. The
  frontend can render the facet **as-is** and echo the selected value straight
  back into `?tag_name=...`.
- **Facet on slugs**: filter values would be stable and language-neutral, but the
  frontend could no longer display facets as-is — it would need a second lookup
  to resolve each slug into a localized label.

Because documents are indexed per translation, `facet_tags_ss` already contains
names in the current locale, so the display-first option works transparently for
each language.

::: warning Localized filter values
The consequence is that **facet filtering is done with the localized tag string**
(e.g. `?tag_name=Actualités` in French, `?tag_name=News` in English). This can
feel odd — the filter value is not a stable identifier and changes per locale, so
a filter URL is language-bound. It is an accepted limitation: Solr facet buckets
cannot hold both a label and a value, and `facet_tags_slugs_ss` is indexed
alongside mainly for slug-based queries, not to correlate names with slugs inside
a facet response (the two facets are independent bucket lists and cannot be
reliably joined).
:::

### Filtering search results

The endpoint accepts optional filter query params, each backed by an event
subscriber listening to `NodeSourceSearchQueryEvent`:

- **`tag_name`** — `NodeSourceSearchTagsFilterSubscriber` restricts results to the
  selected visible tag names (`facet_tags_ss`).
- **`node_type`** — `NodeSourceSearchNodeTypeFilterSubscriber` restricts results to
  the selected node types (`node_type_s`).

Both params accept a single value (`?node_type=Page`) or several
(`?node_type[]=Page&node_type[]=Article`). Values are escaped with Solarium's
`Helper::escapePhrase()` before being turned into filter queries.

::: info Allowlisted node types
`NodesSourcesSearchController::getAllowedNodeTypes()` already emits an allowlist
filter query. Because Solr **ANDs** separate filter queries, the `node_type`
param can only *narrow within* that allowlist — requesting a disallowed type
yields no results rather than exposing restricted content.
:::

You can add your own filters by subscribing to `NodeSourceSearchQueryEvent` and
appending filter queries or facets on the Solarium query:

```php
use RZ\Roadiz\SolrBundle\Event\NodeSourceSearchQueryEvent;
use Solarium\Core\Query\Helper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class CustomSearchFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function onQuery(NodeSourceSearchQueryEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $value = $request?->query->get('my_field');
        if (!is_string($value) || '' === trim($value)) {
            return;
        }

        $helper = new Helper();
        $event->getQuery()->createFilterQuery('my_field')
            ->setQuery('my_field_s:'.$helper->escapePhrase($value));
    }

    public static function getSubscribedEvents(): array
    {
        return [NodeSourceSearchQueryEvent::class => 'onQuery'];
    }
}
```

### Monolog Configuration

Configure a separate log file for Solr operations:

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

## Running Solr with Docker Compose

### Standalone Server

For a simple standalone Solr server:

```yaml
services:
    solr:
        image: solr:9-slim
        volumes:
            - solr:/var/solr
        command:
            - solr-precreate
            - ${SOLR_CORE_NAME}

volumes:
    solr:
```

### SolrCloud with Zookeeper

For a production-ready SolrCloud cluster:

```yaml
services:
    solr:
        image: solr:9-slim
        volumes:
            - solr:/var/solr
        environment:
            ZK_HOST: "zookeeper:2181"
        depends_on: [ zookeeper ]

    zookeeper:
        image: zookeeper:3.9
        volumes:
            - zookeeper-data:/data
            - zookeeper-datalog:/datalog
            - zookeeper-logs:/logs
        environment:
            ZOO_4LW_COMMANDS_WHITELIST: mntr,conf,ruok

volumes:
    solr:
    zookeeper-data:
    zookeeper-datalog:
    zookeeper-logs:
```

## Usage

### Initialize Solr

For SolrCloud mode, create the collection:

```bash
bin/console solr:init
```

This command creates the collection and configures fields and filters. It dispatches a `SolrInitializationEvent` that you can listen to for customization.

### Index Content

Index all NodesSources and Documents:

```bash
bin/console solr:reindex
```

### Drop Collection

To delete a SolrCloud collection:

```bash
bin/console solr:drop
```

## Automated Indexing

The bundle provides a Symfony Scheduler cron task that runs nightly at 3:30 AM to keep your index up to date:

```php
#[AsCronTask(
    expression: '30 3 * * *',
    jitter: 120,
    arguments: '--no-debug -n -q',
)]
```

::: tip
Check your scheduled tasks with:
```bash
bin/console debug:scheduler
```
:::

## Extending Solr Configuration

You can customize Solr field definitions and filters by subscribing to the `SolrInitializationEvent`:

```php
use RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent;
use RZ\Roadiz\SolrBundle\EventListener\AbstractSolrInitializationSubscriber;

class CustomSolrInitializationSubscriber extends AbstractSolrInitializationSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            SolrInitializationEvent::class => 'onSolrInitialization',
        ];
    }

    public function onSolrInitialization(SolrInitializationEvent $event): void
    {
        // Add custom fields
        $this->addField($event, 'custom_field', 'text_general');
        
        // Add custom filters
        $this->addFilter($event, 'customFilter', [
            'class' => 'solr.LowerCaseFilterFactory',
        ]);
    }
}
```

::: tip
See `RZ\Roadiz\SolrBundle\EventListener\DefaultSolrInitializationSubscriber` for examples of extending Solr configuration.
:::

## More Information

For complete documentation on using Apache Solr with Roadiz, see the [Use Apache Solr](../first-steps/use_apache_solr.md) guide and the [Extending Solr](../../extensions/extending_solr.md) guide.
