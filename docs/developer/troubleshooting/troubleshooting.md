---
title: Troubleshooting
next: false
---

# Troubleshooting

## Empty caches manually for an environment

If you experience errors only on a dedicated environment such as `prod`or `dev`, it means that cache is not fresh for these environments.

As a first try, you should always call `bin/console cache:clear;` (replace *prod* by your environment) in command line.

``` shell
bin/console cache:clear --env=prod;
bin/console cache:pool:clear cache.global_clearer --env=prod;
```

## Problem with entities and Doctrine cache?

After each Roadiz **upgrade** you should always upgrade your node-sources entity classes and upgrade database schema.

``` shell
bin/console doctrine:migrations:migrate -n;
bin/console app:install -n;
bin/console cache:clear;
bin/console cache:pool:clear cache.global_clearer;
```
