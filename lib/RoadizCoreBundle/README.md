# Roadiz Core bundle

![Run test status](https://github.com/roadiz/core-bundle/actions/workflows/run-test.yml/badge.svg?branch=develop)

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require roadiz/core-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require roadiz/core-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\CoreBundle\RoadizCoreBundle::class => ['all' => true],
];
```

## Configuration

- Create folders: `public/assets`, `public/themes`, `public/files`, `themes/`, `var/files` for app documents and runtime classes
- Copy and merge `@RoadizCoreBundle/config/packages/*` files into your project `config/packages` folder
- Make to change your `framework.session.name` if you have multiple website running on the same localhost
- Add current bundle and `rezozero/intervention-request-bundle` routes to your project:
```yaml
# config/routes.yaml
roadiz_core:
    resource: "@RoadizCoreBundle/config/routing.yaml"

rz_intervention_request:
    resource: "@RZInterventionRequestBundle/Resources/config/routing.yml"
    prefix:   /
```
