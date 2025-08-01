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

You can use Solr Cloud with a collection instead of a core by setting the `SOLR_COLLECTION_NAME` environment variable and commenting the `core` line.
Then you will need to set the `SOLR_COLLECTION_NUM_SHARDS` and `SOLR_COLLECTION_REPLICATION_FACTOR` variables to configure your collection and execute
`solr:init` command to create the collection.

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
