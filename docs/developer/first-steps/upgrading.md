# Upgrading

::: warning
**Always do a database backup before upgrading.** You can use the `mysqldump`, `mariadb-dump` or `pg_dump` tools  
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

## Versions upgrades

For detailled version upgrades, you can check [Roadiz Github repository](https://github.com/roadiz/core-bundle-dev-app/blob/develop/UPGRADE.md)
