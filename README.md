# Roadiz CoreBundle development app

### Install

- Clone this repository
- Clone roadiz/core-bundle in `lib/RoadizCoreBundle` directory or create a symlink from an existing local repository
- Clone roadiz/compat-bundle in `lib/RoadizCompatBundle` directory or create a symlink from an existing local repository
- Clone roadiz/rozier-bundle in `lib/RoadizRozierBundle` directory or create a symlink from an existing local repository

```shell
cd lib
ln -s ../../rozier-bundle RoadizRozierBundle
```

### Run development server

- Run docker-compose env to get a local database and Solr server
- Run `symfony serve`

### Manual configuration

These require a manual configuration in `config/packages/*.yaml` files and cannot be injected in Container, 
you'll find configuration example in `RoadizCoreBundle/config/packages` and `RoadizCompatBundle/config/packages` folders:

- Translator theme path
- Doctrine ORM mapping
- Doctrine migrations path
- JMS Serializer naming strategy
- Monolog custom doctrine handler
- Roadiz security scheme
- Node workflow
