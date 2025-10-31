# Roadiz development monorepo

[![Unit tests, static analysis and code style](https://github.com/roadiz/core-bundle-dev-app/actions/workflows/run-test.yml/badge.svg?branch=develop)](https://github.com/roadiz/core-bundle-dev-app/actions/workflows/run-test.yml) [![Packages Split](https://github.com/roadiz/core-bundle-dev-app/actions/workflows/split.yaml/badge.svg?branch=develop)](https://github.com/roadiz/core-bundle-dev-app/actions/workflows/split.yaml)

This is **development app** for Roadiz v2. It aggregates all Roadiz bundles and main repositories in one place:

- DocGenerator
- Documents
- DtsGenerator
- EntityGenerator
- Jwt
- Markdown
- Models
- OpenId
- Random
- RoadizCoreBundle
- RoadizFontBundle
- RoadizRozierBundle
- RoadizTwoFactorBundle
- RoadizUserBundle
- RoadizSolrBundle
- Rozier
- and documentation website (./docs)

If you want to start a new headless project with Roadiz, check https://github.com/roadiz/skeleton instead.

## Install

- Clone this repository containing all monorepo packages in `lib` directory
- Checkout `develop` branch
- Initialize `git flow init` to use GitFlow branching model
- Create a `.env.local` file with mandatory `APP_SECRET` and `JWT_PASSPHRASE` vars minimum
- Create a `compose.override.yml` file to expose containers ports
- Run `docker compose run --rm --no-deps --entrypoint= app composer install` to install all dependencies and run scripts inside your Docker container. Symfony packages may add some config files and alter your `compose.yml` file, you can safely rollback to the original one

## Run development server

- Run docker compose to get a local database and Solr server: `docker compose up -d`
- Install Roadiz database fixture: `docker compose exec app bin/console install`
- Install development fixtures: `docker compose exec app bin/console app:install`
- Create a user: `docker compose exec app bin/console users:create -s -b -m $EMAIL $EMAIL`

## Manual configuration

These require a manual configuration in `config/packages/*.yaml` files and cannot be injected in Container, 
you'll find configuration example in `RoadizCoreBundle/config/packages` folder:

- Doctrine ORM mapping
- Doctrine migrations path
- JMS Serializer naming strategy
- Monolog custom doctrine handler
- Roadiz security scheme

## Run tests

```shell
make test
```

Note that _phpstan_ can issue wrong errors if your `lib/*` bundles are symlinked.

## Monorepo tools

Roadiz development env uses: https://github.com/symplify/monorepo-builder

- `vendor/bin/monorepo-builder merge`: Makes sure all your packages deps are in development repository and 
- `vendor/bin/monorepo-builder validate`: Make sure all your packages use the same version
- `vendor/bin/monorepo-builder release patch --dry-run`: List all steps to do when release a new tag (do not actually perform this when using GitFlow)


## Use Frankenphp

Roadiz can be run with [*frankenphp*](https://frankenphp.dev) instead of PHP-FPM + Nginx. If you want to give it a try, override services `app`, `nginx` and `varnish` in your `compose.override.yml`

Use the target `php-dev-franken` instead of `php-dev`.

We use [`dunglas/frankenphp`](https://hub.docker.com/r/dunglas/frankenphp) image with the tag of your PHP version and Debian Bookworm.

Using frankenphp allows you to remove `docker/varnish` and `docker/nginx` folders in your project.

## Use Authentik SSO

Roadiz can be integrated with [*Authentik*](https://goauthentik.io/), allowing users to authenticate via OpenID Connect. This enables seamless and secure login management across your applications.

To set up Authentik with Roadiz, you can use the following command to deploy the necessary services:

```shell
docker compose -f compose.authentik.yml --env-file .env.local up -d
```

This command will start the Authentik services using the configuration defined in compose.authentik.yml while loading environment variables from .env.local.
Once running, you can create an application in Authentik, configure OpenID settings, and enable SSO authentication for Roadiz.

After setup, users will be redirected to Authentik for authentication and automatically logged into Roadiz upon successful verification.

See how to configure it in [Documention](https://docs.roadiz.io/developer/first-steps/manual_config.html#openid-sso-authentication)

## Create new bundle

If you want to create a new bundle in the monorepo, you need to update deptrac configuration to include your new bundle in the analysis.
Edit `deptrac.yaml` file and create a new layer for your bundle, then add it to the appropriate rules.

More info about deptrac configuration can be found in [Deptrac documentation](https://deptrac.github.io/deptrac/concepts/).

Then, run deptrac analysis to make sure your new bundle respects the architecture rules.
```shell
make check-architecture
```

## Run documentation website

```shell
cd docs
# Enable Corepack if not already done
corepack enable
# Install dependencies (Corepack will automatically use the correct pnpm version)
pnpm install
pnpm docs:dev
```

## Backoffice frontend development

The backoffice UI assets are located in the `lib/Rozier` folder.
To launch the frontend development server or build the assets, use **docker compose**:

```shell
docker compose up node

# If you have issues with up command, try:
docker compose run --no-deps --rm --service-ports node pnpm dev --host 0.0.0.0
```

Default command will launch `pnpm dev --host 0.0.0.0` to start a development server on host port 5173 with hot reload.

Do not forget to build final assets for production before shipping a new Roadiz version:

```shell
docker compose run --rm node pnpm build
```

## Documentation development

The documentation is built with Vitepress and files are located in the `docs` folder.
To launch the documentation development server, use **docker compose**:

```shell
docker compose up vitepress
```

Default command will launch `pnpm docs:dev --port 5174 --strictPort 1 --host 0.0.0.0` to start a development server on host port 5174 with hot reload.

Documentation will be available at: http://localhost:5174
