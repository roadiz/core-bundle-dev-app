# Roadiz CoreBundle development app

This is development app for Roadiz v2.x. It aggregate all Roadiz bundle and main repositories in one place.

### Install

- Clone this repository
- Clone roadiz/core-bundle in `lib/RoadizCoreBundle` directory
- Clone roadiz/compat-bundle in `lib/RoadizCompatBundle` directory
- Clone roadiz/rozier-bundle in `lib/RoadizRozierBundle` directory
- Clone roadiz/user-bundle in `lib/RoadizUserBundle` directory
- Clone roadiz/font-bundle in `lib/RoadizFontBundle` directory
- Clone roadiz/rozier in `lib/Rozier` directory
- Clone roadiz/models in `lib/Models` directory

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
