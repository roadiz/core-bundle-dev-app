# Roadiz development monorepo

This is development app for Roadiz v2. It aggregates all Roadiz bundle and main repositories in one place.

### Install

- Clone this repository containing all monorepo packages in `lib` directory

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

Roadiz development env uses: https://github.com/symplify/monorepo-builder

- `vendor/bin/monorepo-builder merge`: Makes sure all your packages deps are in development repository and 
- `vendor/bin/monorepo-builder validate`: Make sure all your packages use the same version
