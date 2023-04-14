# Roadiz Rozier bundle
**Legacy administration interface port to Roadiz v2**

![Run test status](https://github.com/roadiz/rozier-bundle/actions/workflows/run-test.yml/badge.svg?branch=develop)

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require roadiz/rozier-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require roadiz/rozier-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\RozierBundle\RoadizRozierBundle::class => ['all' => true],
];
```

## Configuration

- Copy `config/packages/roadiz_rozier.yaml` to your Symfony app `config/packages` folder.
- Disable Twig `strict_variables`
- Add custom `security` configuration:
```yaml
# config/packages/security.yaml
security:
    enable_authenticator_manager: true
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface:
            algorithm: auto
    providers:
        openid_user_provider:
            id: RZ\Roadiz\OpenId\Authentication\Provider\OpenIdAccountProvider
        roadiz_user_provider:
            entity:
                class: RZ\Roadiz\CoreBundle\Entity\User
                property: username
        all_users:
            chain:
                providers: [ 'openid_user_provider', 'roadiz_user_provider' ]

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: all_users
            switch_user: { role: ROLE_SUPERADMIN, parameter: _su }
            logout:
                path: logoutPage
            custom_authenticator:
                - RZ\Roadiz\RozierBundle\Security\RozierAuthenticator
    access_control:
        - { path: ^/rz-admin/login, roles: PUBLIC_ACCESS }
        - { path: ^/rz-admin, roles: ROLE_BACKEND_USER }
```
- Add custom routes:
```yaml
# config/routes.yaml
roadiz_rozier:
    resource: "@RoadizRozierBundle/config/routing.yaml"

rz_intervention_request:
    resource: "@RZInterventionRequestBundle/Resources/config/routing.yml"
    prefix:   /
```

### OpenID

This bundle can allow users to log in to backoffice using OpenID:

```yaml
#config/packages/roadiz_rozier.yaml
roadiz_rozier:
    #...
    open_id:
        # Verify User info in JWT at each login
        verify_user_info: false
        # Standard OpenID autodiscovery URL, required to enable OpenId login in Roadiz CMS.
        discovery_url: '%env(string:OPEN_ID_DISCOVERY_URL)%'
        # For public identity providers (such as Google), restrict users emails by their domain.
        hosted_domain: '%env(string:OPEN_ID_HOSTED_DOMAIN)%'
        # OpenID identity provider OAuth2 client ID
        oauth_client_id: '%env(string:OPEN_ID_CLIENT_ID)%'
        # OpenID identity provider OAuth2 client secret
        oauth_client_secret: '%env(string:OPEN_ID_CLIENT_SECRET)%'
        granted_roles:
            - ROLE_USER
            - ROLE_BACKEND_USER
```

Then add custom authenticator `roadiz_rozier.open_id.authenticator` to your security configuration:


```yaml
#config/packages/security.yaml
security:
    firewalls:
        main:
            # ...
            custom_authenticator:
                - RZ\Roadiz\RozierBundle\Security\RozierAuthenticator
                - roadiz_rozier.open_id.authenticator
```

## Contributing

Report [issues](https://github.com/roadiz/core-bundle-dev-app/issues) and send [Pull Requests](https://github.com/roadiz/core-bundle-dev-app/pulls) in the [main Roadiz repository](https://github.com/roadiz/core-bundle-dev-app)
