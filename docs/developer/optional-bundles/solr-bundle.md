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

Add the search API operation to expose NodesSources search functionality:

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
