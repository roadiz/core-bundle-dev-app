---
title: Exposing node-types
---

# Exposing node types

All resources configuration files are located in `./config/api_resources` folder.

Here is an example of API resource for a `Page` node-type, you'll find default `operations`.

``` yaml
resources:
    App\GeneratedEntity\NSPage:
        types:
            - Page
        operations:
            ApiPlatform\Metadata\GetCollection:
                method: GET
                shortName: Page
                normalizationContext:
                    enable_max_depth: true
                    groups:
                        - nodes_sources_base
                        - nodes_sources_default
                        - urls
                        - tag_base
                        - translation_base
                        - document_display
                        - document_thumbnails
                        - document_display_sources
                        - nodes_sources_images
                        - nodes_sources_boolean
            _api_page_archives:
                method: GET
                class: ApiPlatform\Metadata\GetCollection
                shortName: Page
                uriTemplate: /pages/archives
                extraProperties:
                    archive_enabled: true
                openapi:
                    summary: 'Retrieve all Page ressources archives months and years'
            ApiPlatform\Metadata\Get:
                method: GET
                shortName: Page
                normalizationContext:
                    groups:
                        - nodes_sources
                        - urls
                        - tag_base
                        - translation_base
                        - document_display
                        - document_thumbnails
                        - document_display_sources
                        - nodes_sources_images
                        - nodes_sources_boolean
```

To automatically generate your resources YAML configuration files, execute the following CLI command:

``` shell
bin/console generate:api-resources
```

If you manage your node-types from your back-office, new node-types configuration files will be generated automatically in the `./config/api_resources` folder.

If you manage your node-types by importing `.json` files, do not forget to execute `bin/console app:migrate` to generate both entities and api-resources.
