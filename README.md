# Roadiz CoreBundle development app

This is development app for Roadiz v2. It aggregates all Roadiz bundle and main repositories in one place.

### Install

- Clone this repository
- Clone roadiz/compat-bundle in `lib/RoadizCompatBundle` directory
- Clone roadiz/core-bundle in `lib/RoadizCoreBundle` directory
- Clone roadiz/doc-generator in `lib/DocGenerator` directory
- Clone roadiz/documents in `lib/Documents` directory
- Clone roadiz/dts-generator in `lib/DtsGenerator` directory
- Clone roadiz/entity-generator in `lib/EntityGenerator` directory
- Clone roadiz/font-bundle in `lib/RoadizFontBundle` directory
- Clone roadiz/jwt in `lib/Jwt` directory
- Clone roadiz/markdown in `lib/Markdown` directory
- Clone roadiz/models in `lib/Models` directory
- Clone roadiz/openid in `lib/OpenId` directory
- Clone roadiz/random in `lib/Random` directory
- Clone roadiz/rozier in `lib/Rozier` directory
- Clone roadiz/rozier-bundle in `lib/RoadizRozierBundle` directory
- Clone roadiz/user-bundle in `lib/RoadizUserBundle` directory

### Generate JWT private and public keys

```shell script
# Generate a strong secret
openssl rand --base64 16; 
# Fill JWT_PASSPHRASE env var in .env.local.
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096;
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout;
```

### Run development server

- Run docker-compose env to get a local database and Solr server
- Run `symfony serve`

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

- `vendor/bin/monorepo-builder merge`: Makes sure all your packages deps are in development repository and 
- `vendor/bin/monorepo-builder validate`: Make sure all your packages use the same version
