# Upgrade to 2.6

## ⚠ Breaking changes

- **Roadiz requires php 8.3 minimum**
- Upgraded to **ApiPlatform 4.x**
- **Dropped Roles entity**, use native Symfony Roles hierarchy to define your roles instead
- **Dropped RoleArrayVoter** BC, you cannot use `isGranted` and `denyUnlessGranted` methods with arrays
- New `CaptchaServiceInterface` to make captcha support any provider service.
- All Solr and SearchEngine related logic has been moved to the new `roadiz/solr-bundle` bundle.
- `ThemeAwareNodeRouter` and `ThemeAwareNodeUrlMatcher` classes have been removed
- All deprecated `AbstractField` constants have been removed (in favor of `FieldType` enum)
- `NodesSourcesRepository::findBySearchQuery` method has been removed to remove dependency on SearchEngine
- `NodesSourcesHeadInterface` has been simplified: `getPolicyUrl`, `getHomePageUrl` and `getHomePage` methods have been removed
- Roadiz Core `solr` configuration has been deprecated, use `nelmio/solarium-bundle` configuration instead.
  - All Solr services now depends on `ClientRegistryInterface`
  - All Solr commands must provide a `clientName` argument to `validateSolrState`.
  - `SolrPaginator` renamed to `SearchEnginePaginator`
  - `SolrSearchListManager` renamed to `SearchEngineListManager`
- `force_locale` and `force_locale_with_urlaliases` Settings have been removed, use `roadiz_core.forceLocale` and `roadiz_core.forceLocaleWithUrlAliases` configuration parameters instead.
- `EmailManager` has been deprecated, use symfony/notifier instead.
- `email_sender` Setting has been removed, use `framework.mailer.envelope.sender` configuration parameter instead.
- `EmailManager::getOrigin()` method has been removed, this will use `framework.mailer.envelope.sender` configuration parameter.

## Upgrade your API Platform configuration

Add `formats` configuration if not already present in your `api_platform.yaml` file.

```yaml
# config/packages/api_platform.yaml
api_platform:
    # ...
    formats:
        jsonld: ['application/ld+json']
        json: ['application/json']
        x-www-form-urlencoded: ['application/x-www-form-urlencoded']
```

And rename `openapiContext` to `openapi` on your api-resources configuration files for each operation.

## Upgrade your Roadiz roles hierarchy

Migrations will automatically convert database roles to JSON roles in users and usergroups tables.
But you need to update your `security.yaml` file to define your roles hierarchy.

```yaml
# config/packages/security.yaml
security:
    role_hierarchy:
        ROLE_PASSWORDLESS_USER:
            - ROLE_PUBLIC_USER
        ROLE_EMAIL_VALIDATED:
            - ROLE_PUBLIC_USER
        ROLE_PUBLIC_USER:
            - ROLE_USER
        ROLE_BACKEND_USER:
            - ROLE_USER
        ROLE_SUPERADMIN:
            - ROLE_PUBLIC_USER
            - ROLE_ACCESS_VERSIONS
            - ROLE_ACCESS_ATTRIBUTES
            - ROLE_ACCESS_ATTRIBUTES_DELETE
            - ROLE_ACCESS_CUSTOMFORMS
            - ROLE_ACCESS_CUSTOMFORMS_RETENTION
            - ROLE_ACCESS_CUSTOMFORMS_DELETE
            - ROLE_ACCESS_DOCTRINE_CACHE_DELETE
            - ROLE_ACCESS_DOCUMENTS
            - ROLE_ACCESS_DOCUMENTS_LIMITATIONS
            - ROLE_ACCESS_DOCUMENTS_DELETE
            - ROLE_ACCESS_DOCUMENTS_CREATION_DATE
            - ROLE_ACCESS_GROUPS
            - ROLE_ACCESS_NODE_ATTRIBUTES
            - ROLE_ACCESS_NODEFIELDS_DELETE
            - ROLE_ACCESS_NODES
            - ROLE_ACCESS_NODES_DELETE
            - ROLE_ACCESS_NODES_SETTING
            - ROLE_ACCESS_NODES_STATUS
            - ROLE_ACCESS_NODETYPES
            - ROLE_ACCESS_NODETYPES_DELETE
            - ROLE_ACCESS_REDIRECTIONS
            - ROLE_ACCESS_SETTINGS
            - ROLE_ACCESS_TAGS
            - ROLE_ACCESS_TAGS_DELETE
            - ROLE_ACCESS_TRANSLATIONS
            - ROLE_ACCESS_USERS
            - ROLE_ACCESS_USERS_DELETE
            - ROLE_ACCESS_WEBHOOKS
            - ROLE_BACKEND_USER
            - ROLE_ACCESS_LOGS
            - ROLE_ACCESS_REALMS
            - ROLE_ACCESS_REALM_NODES
            - ROLE_ACCESS_FONTS
            - ROLE_ALLOWED_TO_SWITCH
```

And remove roles routes from your Roadiz Rozier menu entries:

```diff
 # config/packages/roadiz_rozier.yaml
 roadiz_rozier:
     user_system:
         name: 'user.system'
         route: ~
         icon: uk-icon-rz-users
-        roles: ['ROLE_ACCESS_USERS', 'ROLE_ACCESS_ROLES', 'ROLE_ACCESS_GROUPS']
+        roles: ['ROLE_ACCESS_USERS', 'ROLE_ACCESS_GROUPS']
         subentries:
             manage_users:
                 name: 'manage.users'
                 route: usersHomePage
                 icon: uk-icon-rz-user
                 roles: ['ROLE_ACCESS_USERS']
-            manage_roles:
-                name: 'manage.roles'
-                route: rolesHomePage
-                icon: uk-icon-rz-roles
-                roles: ['ROLE_ACCESS_ROLES']
             manage_groups:
                 name: 'manage.groups'
                 route: groupsHomePage
                 icon: uk-icon-rz-groups
                 roles: ['ROLE_ACCESS_GROUPS']
```

## Upgrade your Roadiz Core bundle configuration

- Add `forceLocale` and `forceLocaleWithUrlAliases` parameters
- Move your Recaptcha configuration to `roadiz_core.recaptcha` parameters
- Replace `$recaptchaPrivateKey` and `$recaptchaPublicKey` constructor arguments with `CaptchaServiceInterface` in your custom services.

```diff
 # config/packages/roadiz_core.yaml
 roadiz_core:
     # ...
     # Force displaying translation locale in every generated node-source paths.
     # This should be enabled if you redirect users based on their language on homepage.
+    forceLocale: false
     # Force displaying translation locale in generated node-source paths even if there is an url-alias in it.
+    forceLocaleWithUrlAliases: false
     # ...
     medias:
         unsplash_client_id: '%env(string:APP_UNSPLASH_CLIENT_ID)%'
         soundcloud_client_id: '%env(string:APP_SOUNDCLOUD_CLIENT_ID)%'
         google_server_id: '%env(string:APP_GOOGLE_SERVER_ID)%'
-        recaptcha_private_key: '%env(string:APP_CAPTCHA_PRIVATE_KEY)%'
-        recaptcha_public_key: '%env(string:APP_CAPTCHA_PUBLIC_KEY)%'
         ffmpeg_path: '%env(string:APP_FFMPEG_PATH)%'
+    captcha:
+        private_key: '%env(string:APP_CAPTCHA_PRIVATE_KEY)%'
+        public_key: '%env(string:APP_CAPTCHA_PUBLIC_KEY)%'
+        verify_url: '%env(string:APP_CAPTCHA_VERIFY_URL)%'
```

## Upgrade your Mailer configuration

```yaml
# config/packages/mailer.yaml
framework:
    # ...
    mailer:
        # Use the default sender address for all emails
        envelope:
            sender: '%env(MAILER_ENVELOP_SENDER)%'
```

```dotenv
###> symfony/mailer ###
MAILER_DSN=smtp://mailer:1025
MAILER_ENVELOP_SENDER="Roadiz Dev Website<roadiz-core-app@roadiz.io>"
###< symfony/mailer ###
```

## Upgrade your email templates

`disclaimer` and `mailContact` variables have been renamed to `email_disclaimer` and `support_email_address` in email templates.
These variables are now automatically provided by RoadizExtension.

## Upgrade your Solr configuration

Roadiz removed *Apache Solr* from its Core bundle. To re-enable it, you need to install the Solr bundle.

```sh
composer require roadiz/solr-bundle
```

- Move your Solr endpoint configuration from `config/packages/roadiz_core.yml` to `config/packages/nelmio_solarium.yaml`
- Use `RZ\Roadiz\SolrBundle\ClientRegistryInterface` to get your Solr client.
- Regenerate your NodesSources entities with `bin/console generate:nsentities` to update repositories `__construct` methods.
- `NodesSourcesRepository::__construct` signature has changed
- `NodesSourcesRepository::findBySearchQuery` method has been removed to remove dependency on SearchEngine.
- All Solr commands have been moved to `RZ\Roadiz\CoreBundle\SearchEngine\Console` namespace.
- `RZ\Roadiz\CoreBundle\Api\ListManager\SolrPaginator` has been renamed to `RZ\Roadiz\CoreBundle\Api\ListManager\SearchEnginePaginator`
- `RZ\Roadiz\CoreBundle\Api\ListManager\SolrSearchListManager` has been renamed to `RZ\Roadiz\CoreBundle\Api\ListManager\SearchEngineListManager`

## Upgrade rezozero/intervention-request-bundle

- Roadiz requires `rezozero/intervention-request-bundle` to `~5.0.1`

## Use composition instead of inheritance for Abstract entities

- All Abstract entities now use composition instead of inheritance.
- Replace extending `AbstractEntity` with `PersistableInterface` and `SequentialIdTrait` in your entities.
- Replace extending `AbstractDateTimed` with `DateTimedInterface` and `DateTimedTrait` in your entities.
- Replace extending `AbstractPositioned` with `PositionedInterface` and `PositionedTrait` in your entities.
- Use `SequentialIdTrait` to provide integer `id` property in your entities.
- Use `UuidTrait` to provide Uuid `id` property in your entities.
- Replace `$this->initAbstractDateTimed();` calls with `$this->initDateTimedTrait();` in your entities.

## Interface changes

- `ExplorerItemInterface::getId()` now returns `string|int|Uuid`

## Removed Themes from routing and events

- `NodesSourcesPathGeneratingEvent` does not have `theme` property anymore.

# Upgrade to 2.5

## Removed node_types and node_type_fields tables

- Make sure to upgrade to **v2.4.11** first. And perform `bin/console nodetypes:export-files` before upgrading to 2.5.
- A backup of your database is highly recommended before upgrading to 2.5.
- Run new migrations

## Removed useless user properties

- Dropped `phone`, `job` and `birthday` columns from users table, they are rarely used and aren't GDPR friendly.

## Upgraded rezozero/intervention-request-bundle

Roadiz requires `rezozero/intervention-request-bundle` to `~4.0.0`
It's possible to remove it from composer.json, and Composer will automatically use the correct version.

## Upgraded jms/serializer-bundle

Roadiz requires `jms/serializer-bundle` to `~5.5.1`
It's possible to remove it from composer.json, and Composer will automatically use the correct version.

# Upgrade to 2.4

## ⚠ Breaking changes

- **Roadiz requires php 8.2 minimum**
- Upgraded to **ApiPlatform 3.3** - requires config changes
  - Prefix all resource files with `resources:` for example:

```yaml
# config/api_resources/node.yml
resources:
    RZ\Roadiz\CoreBundle\Entity\Node:
        operations:
            ApiPlatform\Metadata\Get:
                method: 'GET'
                normalizationContext:
                    groups:
                        - node
                        - tag_base
                        - translation_base
                        - document_display
                        - document_display_sources
                    enable_max_depth: true
```

- Deleted `Controller::findTranslationForLocale`, `Controller::renderJson`, `Controller::denyResourceExceptForFormats`, `Controller::getHandlerFactory`, `Controller::getPreviewResolver` methods
- Deleted deprecated `AppController::makeResponseCachable`
- Removed _sensio/framework-extra-bundle_, upgraded _sentry/sentry-symfony_ and _doctrine/annotations_
- Upgraded _rollerworks/password-strength-bundle_, removed `Top500Provider.php`
- Removed Embed finder for _Twitch_ (they disabled OEmbed on their API)
- All AbstractEmbedFinder sub-classes require HttpClientInterface, dropped GuzzleRequestMessage, changed HttpRequestMessageInterface
- Changed `WebResponseDataTransformerInterface::transform` signature to allow passing an existing WebResponseInterface
- Changed all node exports to CSV format to be able to stream response.
- Pass NodesSources repository entityClass to parent constructor. Changed NodesSourcesRepository constructor signature.
- `AbstractPathNormalizer::__construct` signature changed (added Stopwatch).


# Upgrade to 2.3

## ⚠ Breaking changes

### Switched to **ApiPlatform 3.2**

Make sure to upgrade `bundles.php` file and `api_platform.yaml` configuration:

* Merge `collectionOperations` and `itemOperations` into `operations` for each resource using `ApiPlatform\Metadata\Get` or `ApiPlatform\Metadata\GetCollection` classes
* Regenerate your api platform resource YAML files, or rename `getByPath` operation to `%entity%_get_by_path`

### Other changes
* **Solr:** Removed `$proximity` argument from `search` and `searchWithHighlight` SearchHandlerInterface methods
* Make sure you don't have fields with name longer than 50 characters before migrating. Migration can be skipped if so.
* Removed NodeTypeField `id` join column from NodesCustomForms, NodesSourcesDocuments and NodesToNodes relation tables to use `field_name` string column for loose relation. **Make sure to backup your database before performing this migration**.
* `node_type_name` JSON property is no-longer required in node-type JSON export files.
* **Nodes:** NodesSources `metaKeyword` and Node `priority` fields will be dropped.
* **Settings:** Setting encryption and crypto keys have been dropped, migrate all your secrets to symfony:secrets to get only one secure vault.

Remove any crypto configuration from `config/packages/roadiz_core.yml`:

```yaml
    security:
        private_key_name: default
```
* `getResultItems` method will always return `array<SolrSearchResultItem>` no matter item type or highlighting.
* Command constructor signatures changed
* Controller::get and Controller::has methods have been removed

# Upgrade to 2.2

- Requires PHP 8.1 minimum
- Upgraded to Symfony 6.4 LTS

### Logger configuration

Log namespace changed to `RZ\Roadiz\CoreBundle\Logger\Entity\Log`.
Make sure you update `config/packages/doctrine.yaml` with:

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
    orm:
        auto_generate_proxy_classes: true
        default_entity_manager: default
        entity_managers:
            # Put `logger` entity manager first to select it as default for Log entity
            logger:
                naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
                mappings:
                    ## Just sharding EM to avoid having Logs in default EM
                    ## and flushing bad entities when storing log entries.
                    RoadizCoreLogger:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/vendor/roadiz/core-bundle/src/Logger/Entity'
                        prefix: 'RZ\Roadiz\CoreBundle\Logger\Entity'
                        alias: RoadizCoreLogger
            default:
                dql:
                    string_functions:
                        JSON_CONTAINS: Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonContains
                naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
                auto_mapping: true
                mappings:
                    ## Keep RoadizCoreLogger to avoid creating different migrations since we are using
                    ## the same database for both entity managers. Just sharding EM to avoid
                    ## having Logs in default EM and flushing bad entities when storing log entries.
                    RoadizCoreLogger:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/vendor/roadiz/core-bundle/src/Logger/Entity'
                        prefix: 'RZ\Roadiz\CoreBundle\Logger\Entity'
                        alias: RoadizCoreLogger
                    App:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/src/Entity'
                        prefix: 'App\Entity'
                        alias: App
                    RoadizCoreBundle:
                        is_bundle: true
                        type: attribute
                        dir: 'src/Entity'
                        prefix: 'RZ\Roadiz\CoreBundle\Entity'
                        alias: RoadizCoreBundle
                    RZ\Roadiz\Core:
                        is_bundle: false
                        type: annotation
                        dir: '%kernel.project_dir%/vendor/roadiz/models/src/Core/AbstractEntities'
                        prefix: 'RZ\Roadiz\Core\AbstractEntities'
                        alias: AbstractEntities
                    App\GeneratedEntity:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/src/GeneratedEntity'
                        prefix: 'App\GeneratedEntity'
                        alias: App\GeneratedEntity
	            # ...
```


# Upgrade to 2.1

First Roadiz version to use a monorepository structure. All Roadiz components are now in the same `lib` folder (except for nodetype-contracts).

## ⚠ Breaking changes

`roadiz/models` namespace root is now ./src. Change your Doctrine entities path:

```yaml
RZ\Roadiz\Core:
    is_bundle: false
    type: attribute
    dir: '%kernel.project_dir%/vendor/roadiz/models/src/Roadiz/Core/AbstractEntities'
    prefix: 'RZ\Roadiz\Core\AbstractEntities'
    alias: AbstractEntities
```

### ApiPlatform 2.7
You must migrate your `config/api_resources/*.yml` files to use new [ApiPlatform interfaces and resource YML syntax](https://api-platform.com/docs/core/upgrade-guide/#summary-of-the-changes-between-26-and-2730)

- Remove and regenerate your NS entities with `bin/console generate:nsentities` to update namespaces
- Remove and regenerate your Resource configs with `bin/console generate:api-resources`
    - If you do not want to remove existing config, [you'll have to move `itemOperations` and `collectionOperations` to single `operations` node](https://api-platform.com/docs/core/upgrade-guide/#metadata-changes) and add `class` with `ApiPlatform\Metadata\Get` or `ApiPlatform\Metadata\GetCollection`
    - Rename `iri` to `types` and wrap single values into array
    - Rename `path` to `uriTemplate`
    - Rename `normalization_context` to `normalizationContext`
    - Rename `openapi_context` to `openapiContext`
    - Move `shortName` to each `operation`
    - Rename `attributes` to `extraProperties` (for `/archives` endpoints)
    - Add `uriTemplate` for your custom endpoints (for `/archives` endpoints)
    - Prefix all named operations with `api_` to  avoid conflict with non API routes
- All filters and extensions use new interfaces
- Removed all deprecated DataTransformer and Dto classes
- Once everything is migrated changed `metadata_backward_compatibility_layer: false` in `config/packages/api_platform.yaml`
