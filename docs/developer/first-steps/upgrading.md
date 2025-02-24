# Upgrading

::: warning
**Always do a database backup before upgrading.** You can use the `mysqldump` or `pg_dump` tools  
to quickly export your database as a file.

- With a MySQL server:  
  ```bash
  mysqldump -u[user] -p[user_password] [database_name] > dumpfilename.sql
  ```
- With a PostgreSQL server:
  ```bash
  pg_dump -U [user] [database_name] -f dumpfilename.sql
  ```
:::

## Updating Roadiz

Use **Composer** to update dependencies or Roadiz itself with **Standard** or **Headless** editions.  
Make sure that your Roadiz *version constraint* is set in your project's `composer.json` file, then:

```bash
composer update -o;
```

Run database registered migrations (some will be skipped according to your database type).  
Doctrine migrations are the default method to upgrade all non-node-type-related entities:

```bash
bin/console doctrine:migrations:migrate;
```

To avoid losing sensitive node-sources data, regenerate your node-source entities class files:

```bash
bin/console generate:nsentities;
```

Then, check if there are pending SQL changes due to your Roadiz node-types.  
This should be addressed with `doctrine:migrations:migrate`, but you can check with:

```bash
bin/console doctrine:schema:update --dump-sql;
# Upgrade node-sources tables if necessary
bin/console doctrine:schema:update --dump-sql --force;
```

Finally, clear your app caches:

```bash
# Clear cache for each environment
bin/console cache:clear -e dev
bin/console cache:clear -e prod
bin/console cache:pool:clear cache.global_clearer
bin/console messenger:stop-workers
```

::: tip
If you are using a runtime cache like OPcache or APCu, you’ll need to purge it manually  
because it can't be done from a CLI interface. As a last resort, restart your `php-fpm` service.
:::

---

## Upgrading from Roadiz v2.1 to v2.2

For full details, check the [Changelog](https://github.com/roadiz/core-bundle-dev-app/blob/main/CHANGELOG.md#v220-2023-12-12).

### Key changes:

- **Doctrine migrations** are now the default method to upgrade all node-type-related entities.  
  Run the following command after updating your Roadiz dependencies:

  ```bash
  bin/console doctrine:migrations:migrate;
  ```

- **Roadiz updated to API Platform new version and Metadata scheme.**  
  You must rewrite your API resource YAML files to match the new scheme.  
  See the [API Platform upgrade guide](https://api-platform.com/docs/core/upgrade-guide/).

  To regenerate API resources (this will remove any custom serialization groups):

  ```bash
  bin/console generate:api-resources;
  ```

- **Node-type updates after Roadiz 2.2 will be versioned** and **will generate a Doctrine migration file.**  
  If needed, generate a migration file for existing node-types:

  ```bash
  bin/console doctrine:migrations:generate;
  ```

- **Entities path change for `roadiz/models`**:  
  From
  ```
  %kernel.project_dir%/vendor/roadiz/models/src/Roadiz/Core/AbstractEntities
  ```
  To
  ```
  %kernel.project_dir%/lib/Models/src/Core/AbstractEntities
  ```

- **`Logger` now has a separate entity manager** to avoid persisting invalid log entries.

### Example configuration:

```yaml
orm:
    auto_generate_proxy_classes: true
    default_entity_manager: default
    entity_managers:
        # Put `logger` entity manager first to select it as default for Log entity
        logger:
            naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
            mappings:
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
```