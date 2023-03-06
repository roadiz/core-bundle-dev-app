# Roadiz User bundle
**Public user management bundle for Roadiz v2**

![Run test status](https://github.com/roadiz/user-bundle/actions/workflows/run-test.yml/badge.svg?branch=develop)

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require roadiz/user-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require roadiz/user-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\UserBundle\RoadizUserBundle::class => ['all' => true],
];
```

## Configuration

- Copy *API Platform* resource configuration file: `./config/api_resources/user.yaml` to your Roadiz project `api_resource` folder.
- Edit your `./config/packages/framework.yaml` file with:
```yaml
framework:
    rate_limiter:
        user_signup:
            policy: 'token_bucket'
            limit: 5
            rate: { interval: '1 minutes', amount: 3 }
            cache_pool: 'cache.user_signup_limiter'
        password_request:
            policy: 'token_bucket'
            limit: 3
            rate: { interval: '1 minutes', amount: 3 }
            cache_pool: 'cache.password_request_limiter'
        password_reset:
            policy: 'token_bucket'
            limit: 3
            rate: { interval: '1 minutes', amount: 3 }
            cache_pool: 'cache.password_reset_limiter'
```
- Edit your `./config/packages/cache.yaml` file with:
```yaml
framework:
    cache:
        pools:
            cache.user_signup_limiter: ~
            cache.password_request_limiter: ~
            cache.password_reset_limiter: ~
```
- Edit your `./config/packages/security.yaml` file with:
```yaml
security:
    access_control:
        # Append user routes configuration
        - { path: "^/api/users/signup", methods: [ POST ], roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: "^/api/users/password_request", methods: [ POST ], roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: "^/api/users/password_reset", methods: [ PUT ], roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: "^/api/users", methods: [ GET, PUT, PATCH, POST ], roles: ROLE_USER }
```
- Edit your `./.env` file with:
```dotenv
USER_PASSWORD_RESET_URL=https://your-public-url.test/reset
USER_VALIDATION_URL=https://your-public-url.test/validate
USER_PASSWORD_RESET_EXPIRES_IN=600
USER_VALIDATION_EXPIRES_IN=3600
```
- Update your CORS configuration with additional headers `Www-Authenticate` and `x-g-recaptcha-response`:
```yaml
# config/packages/nelmio_cors.yaml
nelmio_cors:
    defaults:
        # ...
        allow_headers: ['Content-Type', 'Authorization', 'Www-Authenticate', 'x-g-recaptcha-response']
        expose_headers: ['Link', 'Www-Authenticate']
```


## Maintenance commands

- `bin/console users:purge-validation-tokens`: Delete all expired user validation tokens
