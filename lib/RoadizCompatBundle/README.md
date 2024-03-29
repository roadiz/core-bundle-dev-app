# Roadiz compatibility bundle
**Adds a thin compatibility layer between Roadiz v2 and legacy themes**

![Run test status](https://github.com/roadiz/compat-bundle/actions/workflows/run-test.yml/badge.svg?branch=develop)

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require roadiz/compat-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require roadiz/compat-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\CompatBundle\RoadizCompatBundle::class => ['all' => true],
];
```

## Contributing

Report [issues](https://github.com/roadiz/core-bundle-dev-app/issues) and send [Pull Requests](https://github.com/roadiz/core-bundle-dev-app/pulls) in the [main Roadiz repository](https://github.com/roadiz/core-bundle-dev-app)
