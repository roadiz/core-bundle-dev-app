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

### DotEnv variables

```dotenv
###> nelmio/solarium-bundle ###
SOLR_HOST=solr
SOLR_PORT=8983
SOLR_PATH=/
SOLR_CORE_NAME=roadiz
SOLR_COLLECTION_NUM_SHARDS=1
SOLR_COLLECTION_REPLICATION_FACTOR=1
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
    clients:
        default:
            endpoints: [default]
            # You can customize the http timeout (in seconds) here. The default is 5sec.
            adapter_timeout: 5
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

### Crontab

You can add a crontab entry to reindex all your website content every day at midnight:
```text
0 0 * * *    /usr/local/bin/php -d memory_limit=-1 /app/bin/console solr:reindex --no-debug -n -q
```

## Contributing

Report [issues](https://github.com/roadiz/core-bundle-dev-app/issues) and send [Pull Requests](https://github.com/roadiz/core-bundle-dev-app/pulls) in the [main Roadiz repository](https://github.com/roadiz/core-bundle-dev-app)
