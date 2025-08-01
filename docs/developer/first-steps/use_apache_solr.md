# Use _Apache Solr_ as main Roadiz search engine

Roadiz can index and search NodesSources and Documents using the _Apache Solr_ search engine.
This allows you to perform full-text search on your content and provides advanced search capabilities.

::: info
**Since version 2.6**, Roadiz moved *Apache Solr* from its Core bundle to a separate bundle:
```sh
composer require roadiz/solr-bundle
```

And it supports both _Solr standalone core_ and _SolrCloud mode_ with collections.
:::

## Configuration

Roadiz Solr bundle requires *Nelmio Solarium* package to provide a Solarium API client.
If Symfony Flex recipe didn't already do it, add *dotenv* variables to your `.env` file or `.env.local` file to configure your Solr instance.

In this example, we assume you are using _SolrCloud mode_ in a Docker compose environment with a collection named `roadiz`:

```dotenv
###> nelmio/solarium-bundle ###
SOLR_HOST=solr
SOLR_PORT=8983
SOLR_PATH=/
# Use Solr standalone core, use SOLR_CORE_NAME
SOLR_CORE_NAME=roadiz
# Use SolrCloud mode, use SOLR_COLLECTION_NAME
SOLR_COLLECTION_NAME=roadiz
SOLR_COLLECTION_NUM_SHARDS=1
SOLR_COLLECTION_REPLICATION_FACTOR=1
SOLR_SECURE=0
###< nelmio/solarium-bundle ###
```

Then configure your default endpoint in `config/packages/nelmio_solarium.yaml`:

```yaml
# config/packages/nelmio_solarium.yaml
nelmio_solarium:
    endpoints:
        default:
            # We use Solr Cloud with collection
            host: '%env(SOLR_HOST)%'
            port: '%env(int:SOLR_PORT)%'
            path: '%env(SOLR_PATH)%'
            core: '%env(SOLR_COLLECTION_NAME)%'
    clients:
        default:
            endpoints: [default]
            # You can customize the http timeout (in seconds) here. The default is 5sec.
            adapter_timeout: 5
```

You can use Solr in 2 ways: as a core or as a collection:
- If you are using Solr as a single core, you can set the `SOLR_CORE_NAME` environment variable.
- If you are using _SolrCloud mode_, you can set the `SOLR_COLLECTION_NAME`

::: info
When using _SolrCloud mode_ you will need to set the `SOLR_COLLECTION_NUM_SHARDS` and 
`SOLR_COLLECTION_REPLICATION_FACTOR` variables to configure your collection and execute
`solr:init` command to create the collection.
:::

### Register an API operation for search

Add `api_nodes_sources_search` API operation to expose `NodesSourcesSearchController`
```yaml
# config/api_resources/nodes_sources.yml
resources:
    RZ\Roadiz\CoreBundle\Entity\NodesSources:
        operations:
            api_nodes_sources_search:
                class: ApiPlatform\Metadata\GetCollection
                method: 'GET'
                uriTemplate: '/nodes_sources/search'
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
                    parameters:
                        -   type: string
                            name: search
                            in: query
                            required: true
                            description: Search pattern
                            schema:
                                type: string
```

### Register a Monolog handler

Add a solr handler to your monolog config if you want to separate its logs in a different file.
```yaml
# config/packages/monolog.yaml
monolog:
    channels:
        # ...
        # Add a new channel for Solr logs
        - searchEngine
    handlers:
        solr:
            type: stream
            path: "%kernel.logs_dir%/solr.%kernel.environment%.log"
            level: debug
            channels: ["searchEngine"]
```

## Run Solr with Docker compose

You can run Solr using Docker Compose. Below are two configurations: one for a single standalone server and another for a Solr Cloud cluster with Zookeeper
based on [official documentation](https://solr.apache.org/guide/solr/latest/deployment-guide/solr-in-docker.html):

### Single standalone server

```yaml
services:
    # ...
    # https://solr.apache.org/guide/solr/latest/deployment-guide/solr-in-docker.html
    solr:
        image: solr:9-slim
        volumes:
            - solr:/var/solr
        command:
            - solr-precreate
            - ${SOLR_CORE_NAME}

volumes:
    # ...
    solr:
```

### Solr Cloud cluster with Zookeeper

```yaml
services:
    # ...
    
    # Solr Cloud requires zoo to run
    # https://solr.apache.org/guide/solr/latest/deployment-guide/solr-in-docker.html
    solr:
        image: solr:9-slim
        volumes:
            - solr:/var/solr
        environment:
            ZK_HOST: "zoo:2181"
        depends_on: [ zoo ]

    zoo:
        image: zookeeper:3.9
        volumes:
            - zoo-data:/data
            - zoo-datalog:/datalog
            - zoo-logs:/logs
        environment:
            ZOO_4LW_COMMANDS_WHITELIST: mntr,conf,ruok

volumes:
    # ...
    solr:
    zoo-data:
    zoo-datalog:
    zoo-logs:
```

## Specific commands for SolrCloud mode

When using _SolrCloud mode_, you can use the following commands to manage your Solr collection.

### Initialize Solr Collection

Using _SolrCloud mode_ requires you to create a collection before indexing your NodesSources:

```shell
# Initialize Solr collection (for Solr Cloud)
# and updates fields and filters
bin/console solr:init
```

This command dispatches a `RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent` event to allow you to customize your collection fields types.

### Drop Solr Collection

When using _SolrCloud mode_ you can drop the collection using:

```shell
bin/console solr:drop
```

## Index your project content

Then you can reindex all NodesSources, Documents to populate the index with existing data:

```shell
# Reindex all NodesSources
bin/console solr:reindex
```

## Cron task

This bundle provides a new Scheduler `CronTask` to update your Solr index each night at 3:30AM:

```php
#[AsCronTask(
    expression: '30 3 * * *',
    jitter: 120,
    arguments: '--no-debug -n -q',
)]
```

::: tip
You can check if Solr reindex command is well registered in your scheduler by running:

```shell
bin/console debug:scheduler
# Or if you are using Docker Compose:
docker compose exec app bin/console debug:scheduler
```
:::

## Extending Solr configuration

If you want to add/remove fields and update filters during `solr:init` command, you can subscribe to the `RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent` event.
An abstract subscriber is provided in the bundle to provide helper methods to add fields and filters: `RZ\Roadiz\SolrBundle\EventListener\AbstractSolrInitializationSubscriber`.

::: tip
You can take a look at the `RZ\Roadiz\SolrBundle\EventListener\DefaultSolrInitializationSubscriber` class to see how to extend Solr configuration.
:::
