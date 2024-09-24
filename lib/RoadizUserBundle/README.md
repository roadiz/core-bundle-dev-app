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

- Copy *API Platform* resource configuration files to your Roadiz project `api_resource` folder: 
    - `./config/api_resources/user.yaml` 
    - `./config/api_resources/me.yaml` 
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
        # Prepend user routes configuration before API Platform ones
        # Public routes must be defined before protected ones
        - { path: "^/api/users/login_link_check", methods: [ POST ], roles: PUBLIC_ACCESS }
        - { path: "^/api/users/login_link", methods: [ POST ], roles: PUBLIC_ACCESS }
        - { path: "^/api/users/signup", methods: [ POST ], roles: PUBLIC_ACCESS }
        - { path: "^/api/users/password_request", methods: [ POST ], roles: PUBLIC_ACCESS }
        - { path: "^/api/users/password_reset", methods: [ PUT ], roles: PUBLIC_ACCESS }
        # ...
        - { path: "^/api", roles: ROLE_BACKEND_USER, methods: [ POST, PUT, PATCH, DELETE ] }
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

## Passwordless user creation and authentication

You can switch your public users to `PasswordlessUser` and set up a login link authentication process along with
user creation process.

First you need to configure a public login link route:

```yaml
# config/routes.yaml
public_login_link_check:
    path: /api/users/login_link_check
    methods: [POST]
```

Then you need to configure your security.yaml file to use `login_link` authentication process in your API firewall.
You **must** use `all_users` provider to be able to use Roadiz User provider during the login_link authentication process.

```yaml
# config/packages/security.yaml
# https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/8-jwt-user-provider.html#symfony-5-3-and-higher
api:
    pattern: ^/api
    stateless: true
    # We need to use all_users provider to be able to use Roadiz User provider 
    # during the login_link authentication process
    provider: all_users
    jwt: ~
    login_link:
        check_route: public_login_link_check
        check_post_only: true
        success_handler: lexik_jwt_authentication.handler.authentication_success
        failure_handler: lexik_jwt_authentication.handler.authentication_failure
        signature_properties: [ 'email' ]
        # lifetime in seconds
        lifetime: 600
        max_uses: 3
```

### Public login link creation

Then you'll need a public route to request a login-link. In your project create a new `App\Controller\SecurityController`
and add a new route `/api/users/login_link`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use RZ\Roadiz\CoreBundle\Repository\UserRepository;
use RZ\Roadiz\CoreBundle\Security\LoginLink\LoginLinkSenderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

final readonly class SecurityController
{
    public function __construct(
        private LoginLinkSenderInterface $loginLinkSender,
    ) {
    }
    
    #[Route('/api/users/login_link', name: 'public_user_login_link_request', methods: ['POST'])]
    public function requestLoginLink(
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        Request $request
    ): Response {
        // load the user in some way (e.g. using the form input)
        $email = $request->getPayload()->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            // Do not leak if a user exists or not
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        // create a login link for $user this returns an instance
        // of LoginLinkDetails
        $loginLinkDetails = $loginLinkHandler->createLoginLink($user, $request);
        $this->loginLinkSender->sendLoginLink($user, $loginLinkDetails);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
```

Register your controller:

```yaml
# config/services.yaml
services:
    App\Controller\SecurityController:
        tags: [ 'controller.service_arguments' ]
```

### Override login link URL

Roadiz User Bundle provides a custom `Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface` service to generate a login-link  with a different **base-uri**,
all you need is to register `RZ\Roadiz\UserBundle\Security\Security\FrontendLoginLinkHandler` service in your project with its mandatory arguments:

```yaml
# config/services.yaml
services:
    RZ\Roadiz\UserBundle\Security\Security\FrontendLoginLinkHandler:
    $decorated: '@RZ\Roadiz\UserBundle\Security\Security\FrontendLoginLinkHandler.inner'
    arguments:
        $decorated: '@App\Security\LoginLinkHandler.inner'
        $frontendLoginCheckRoute: '%frontend_login_check_route%'
        $frontendLoginLinkRequestRoutes:
            - 'frontend_login_link_request_route'
            - 'another_login_link_request_route'
        $signatureHasher: '@security.authenticator.login_link_signature_hasher.api_login_link'
```
Now for each `$frontendLoginLinkRequestRoutes` login_link will be generated using `$frontendLoginCheckRoute` base URL

## Public users roles

- `ROLE_PUBLIC_USER`: Default role for public users
- `ROLE_PASSWORDLESS_USER`: Role for public users authenticated with a login link
- `ROLE_EMAIL_VALIDATED`: Role for public users added since they validated their email address, through a validation token or a login link

## Maintenance commands

- `bin/console users:purge-validation-tokens`: Delete all expired user validation tokens
- `bin/console users:inactive -d 60 -r ROLE_PUBLIC_USER -m ROLE_EMAIL_VALIDATED -v`: Delete all inactive **public** users that did not logged-in for 60 days. Notice that this command example only displays users that do not have `ROLE_EMAIL_VALIDATED` role.

## Contributing

Report [issues](https://github.com/roadiz/core-bundle-dev-app/issues) and send [Pull Requests](https://github.com/roadiz/core-bundle-dev-app/pulls) in the [main Roadiz repository](https://github.com/roadiz/core-bundle-dev-app)
