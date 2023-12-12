# Roadiz Two-factor authentication bundle

![Run test status](https://github.com/roadiz/two-factor-bundle/actions/workflows/run-test.yml/badge.svg?branch=develop)

This bundle provides a two-factor authentication system for Roadiz CMS. Based on [scheb/two-factor-bundle](https://github.com/scheb/2fa) bundle.

- OTP (One Time Password) authentication with Google Authenticator
- Backup codes (hashed and stored in database)
- Trusted devices (remembered for a configurable amount of time)
- Use `APP_NAMESPACE`, `APP_TITLE` and `APP_SECRET` environment variables 

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require roadiz/two-factor-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require roadiz/two-factor-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\TwoFactorBundle\RoadizTwoFactorBundle::class => ['all' => true],
];
```

## Configuration

- Copy and merge `@RoadizTwoFactor/config/packages/scheb_2fa.yaml` files into your project `config/packages` folder
- Add this bundle routes to your project `config/routes.yaml` file:
```yaml
# config/routes.yaml
roadiz_two_factor:
    resource: "@RoadizTwoFactorBundle/config/routing.yaml"
```

## Contributing

Report [issues](https://github.com/roadiz/core-bundle-dev-app/issues) and send [Pull Requests](https://github.com/roadiz/core-bundle-dev-app/pulls) in the [main Roadiz repository](https://github.com/roadiz/core-bundle-dev-app)
