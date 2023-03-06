# Roadiz Font bundle

![Run test status](https://github.com/roadiz/font-bundle/actions/workflows/run-test.yml/badge.svg?branch=develop)

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require roadiz/font-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require roadiz/font-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\FontBundle\RoadizFontBundle::class => ['all' => true],
];
```

## Configuration

- Create folders: `var/files/fonts` for fonts storage
- Add Flysystem storage definition
```yaml
# config/packages/flysystem.yaml
flysystem:
    storages:
        font.storage:
            adapter: 'local'
            options:
                directory: '%kernel.project_dir%/var/files/fonts'
```
- Copy and merge `@RoadizFontBundle/config/packages/*` files into your project `config/packages` folder
```yaml
# config/routes.yaml
roadiz_font:
    resource: "@RoadizFontBundle/config/routing.yaml"
```
- Add bundle to doctrine entity mapping
```yaml
doctrine:
    orm:
        mappings:
            RoadizFontBundle:
                is_bundle: true
                type: attribute
                dir: 'src/Entity'
                prefix: 'RZ\Roadiz\FontBundle\Entity'
                alias: RoadizFontBundle
```
- Create a new Roadiz role: `ROLE_ACCESS_FONTS`
- Add new `roadiz_rozier` admin sub-entry
```yaml
---
roadiz_rozier:
    entries:
        construction:
            subentries:
                manage_fonts:
                    name: 'manage.fonts'
                    route: fontsHomePage
                    icon: 'uk-icon-rz-fontes'
                    roles: ['ROLE_ACCESS_FONTS']
```
- Perform *Doctrine Migrations* to create `fonts` table
