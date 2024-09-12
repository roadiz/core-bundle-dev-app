# Roadiz development monorepo

[![Unit tests, static analysis and code style](https://github.com/roadiz/core-bundle-dev-app/actions/workflows/run-test.yml/badge.svg?branch=develop)](https://github.com/roadiz/core-bundle-dev-app/actions/workflows/run-test.yml) [![Packages Split](https://github.com/roadiz/core-bundle-dev-app/actions/workflows/split.yaml/badge.svg?branch=develop)](https://github.com/roadiz/core-bundle-dev-app/actions/workflows/split.yaml)

This is development app for Roadiz v2. It aggregates all Roadiz bundle and main repositories in one place:

- DocGenerator
- Documents
- DtsGenerator
- EntityGenerator
- Jwt
- Markdown
- Models
- OpenId
- Random
- RoadizCompatBundle
- RoadizCoreBundle
- RoadizFontBundle
- RoadizRozierBundle
- RoadizTwoFactorBundle
- RoadizUserBundle
- Rozier

### Install

- Clone this repository containing all monorepo packages in `lib` directory
- Checkout `develop` branch
- Initialize `git flow init` to use GitFlow branching model
- Create a `.env.local` file with mandatory `APP_SECRET` and `JWT_PASSPHRASE` vars minimum
- Create a `compose.override.yaml` file to expose containers ports
- Run `composer install` to install all dependencies and run scripts. Symfony packages may add some config files and alter your `compose.yml` file, you can safely rollback to the original one

### Run development server

- Run docker compose to get a local database and Solr server: `docker compose up -d`
- Install Roadiz database fixture: `docker compose exec -u www-data app bin/console install`
- Install development fixtures: `docker compose exec -u www-data app bin/console app:install`
- Create a user: `docker compose exec -u www-data app bin/console users:create -s -b -m $EMAIL $EMAIL`

### Manual configuration

These require a manual configuration in `config/packages/*.yaml` files and cannot be injected in Container, 
you'll find configuration example in `RoadizCoreBundle/config/packages` and `RoadizCompatBundle/config/packages` folders:

- Doctrine ORM mapping
- Doctrine migrations path
- JMS Serializer naming strategy
- Monolog custom doctrine handler
- Roadiz security scheme

### Run tests

```shell
make test
```

Note that _phpstan_ can issue wrong errors if your `lib/*` bundles are symlinked.

### Monorepo tools

Roadiz development env uses: https://github.com/symplify/monorepo-builder

- `vendor/bin/monorepo-builder merge`: Makes sure all your packages deps are in development repository and 
- `vendor/bin/monorepo-builder validate`: Make sure all your packages use the same version
- `vendor/bin/monorepo-builder release patch --dry-run`: List all steps to do when release a new tag (do not actually perform this when using GitFlow)
