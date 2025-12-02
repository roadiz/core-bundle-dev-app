# User Bundle

The Roadiz User Bundle provides a comprehensive public user management system for your Roadiz application. It handles user registration, authentication, password reset, and account management for front-end users.

## Features

- **User Registration**: Sign-up functionality with email validation
- **Authentication**: Traditional password-based and passwordless login options
- **Password Management**: Password reset and change functionality
- **Email Validation**: Token-based email verification system
- **Login Links**: Passwordless authentication via email magic links
- **Rate Limiting**: Built-in protection against brute-force attacks
- **User Roles**: Flexible role-based access control for public users

## Installation

Install the bundle using Composer:

```bash
composer require roadiz/user-bundle
```

If you're not using Symfony Flex, you'll need to manually enable the bundle in `config/bundles.php`:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\UserBundle\RoadizUserBundle::class => ['all' => true],
];
```

## Configuration

### Step 1: Copy API Platform Resources

Copy the API Platform resource configurations to your project:

```bash
cp vendor/roadiz/user-bundle/config/api_resources/user.yaml config/api_resources/
cp vendor/roadiz/user-bundle/config/api_resources/me.yaml config/api_resources/
```

### Step 2: Configure Rate Limiters

Add rate limiter configuration to `config/packages/framework.yaml`:

```yaml
# config/packages/framework.yaml
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

### Step 3: Configure Cache Pools

Add cache pools in `config/packages/cache.yaml`:

```yaml
# config/packages/cache.yaml
framework:
    cache:
        pools:
            cache.user_signup_limiter: ~
            cache.password_request_limiter: ~
            cache.password_reset_limiter: ~
```

### Step 4: Configure Security Access Control

Update `config/packages/security.yaml` to define public and protected routes:

```yaml
# config/packages/security.yaml
security:
    access_control:
        # Prepend user routes configuration before API Platform ones
        # Public routes must be defined before protected ones
        - { path: "^/api/users/login_link_check", methods: [ POST ], roles: PUBLIC_ACCESS }
        - { path: "^/api/users/login_link", methods: [ POST ], roles: PUBLIC_ACCESS }
        - { path: "^/api/users/signup", methods: [ POST ], roles: PUBLIC_ACCESS }
        - { path: "^/api/users/password_request", methods: [ POST ], roles: PUBLIC_ACCESS }
        - { path: "^/api/users/password_reset", methods: [ PUT ], roles: PUBLIC_ACCESS }
        # Protected routes
        - { path: "^/api", roles: ROLE_BACKEND_USER, methods: [ POST, PUT, PATCH, DELETE ] }
        - { path: "^/api/users", methods: [ GET, PUT, PATCH, POST ], roles: ROLE_USER }
```

### Step 5: Configure Environment Variables

Add the following to your `.env` or `.env.local` file:

```dotenv
# User bundle configuration
USER_PASSWORD_RESET_URL=https://your-public-url.test/reset
USER_VALIDATION_URL=https://your-public-url.test/validate
USER_PASSWORD_RESET_EXPIRES_IN=600
USER_VALIDATION_EXPIRES_IN=3600
```

Replace the URLs with your actual front-end application URLs where users will complete password reset and email validation.

### Step 6: Configure CORS

Update CORS configuration to allow required headers:

```yaml
# config/packages/nelmio_cors.yaml
nelmio_cors:
    defaults:
        allow_headers: ['Content-Type', 'Authorization', 'Www-Authenticate', 'x-g-recaptcha-response']
        expose_headers: ['Link', 'Www-Authenticate']
```

## User Roles

The bundle provides the following user roles:

- **`ROLE_PUBLIC_USER`**: Default role for all public users
- **`ROLE_PASSWORDLESS_USER`**: Role for users authenticated via login link
- **`ROLE_EMAIL_VALIDATED`**: Role assigned after email validation

## Passwordless Authentication

The bundle supports passwordless authentication using login links sent via email.

### Configuration

1. Add the login link check route to `config/routes.yaml`:

```yaml
# config/routes.yaml
public_login_link_check:
    path: /api/users/login_link_check
    methods: [POST]
```

2. Configure the security firewall in `config/packages/security.yaml`:

```yaml
# config/packages/security.yaml
security:
    providers:
        # Combined provider for both backend and public users
        all_users:
            chain:
                providers: ['roadiz_user_provider', 'roadiz_backend_user_provider']
    
    firewalls:
        api:
            pattern: ^/api
            stateless: true
            provider: all_users
            jwt: ~
            login_link:
                check_route: public_login_link_check
                check_post_only: true
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure
                signature_properties: [ 'email' ]
                lifetime: 600
                max_uses: 3
```

### Creating a Login Link Controller

Create a controller to handle login link requests:

```php
<?php
// src/Controller/SecurityController.php

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
        $email = $request->getPayload()->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            // Do not leak if a user exists or not
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        $loginLinkDetails = $loginLinkHandler->createLoginLink($user, $request);
        $this->loginLinkSender->sendLoginLink($user, $loginLinkDetails);
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
```

Register the controller in `config/services.yaml`:

```yaml
# config/services.yaml
services:
    App\Controller\SecurityController:
        tags: [ 'controller.service_arguments' ]
```

### Customizing Login Link URLs

To use a different base URL for login links (e.g., your front-end application):

```yaml
# config/services.yaml
services:
    RZ\Roadiz\UserBundle\Security\FrontendLoginLinkHandler:
        decorates: Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface
        arguments:
            $decorated: '@RZ\Roadiz\UserBundle\Security\FrontendLoginLinkHandler.inner'
            $frontendLoginCheckRoute: '%frontend_login_check_route%'
            $frontendLoginLinkRequestRoutes:
                - 'frontend_user_login_link_request'
                - 'public_user_login_link_request'
                - 'api_user_signup'
            $signatureHasher: '@security.authenticator.login_link_signature_hasher.api_login_link'
```

## Maintenance Commands

The bundle provides useful maintenance commands:

### Purge Expired Validation Tokens

Remove expired user validation tokens:

```bash
bin/console users:purge-validation-tokens
```

### Remove Inactive Users

Delete inactive users who haven't logged in for a specified period:

```bash
# Delete public users inactive for 60 days without ROLE_EMAIL_VALIDATED
bin/console users:inactive -d 60 -r ROLE_PUBLIC_USER -m ROLE_EMAIL_VALIDATED -v
```

Options:
- `-d, --days`: Number of days of inactivity (default: 60)
- `-r, --role`: Filter by role (can be used multiple times)
- `-m, --missing-role`: Filter by missing role
- `-v`: Dry run (display users without deleting)

::: tip
Run maintenance commands regularly using cron jobs or Symfony Scheduler to keep your user database clean.
:::

## API Endpoints

The bundle provides the following API endpoints:

- `POST /api/users/signup`: User registration
- `POST /api/users/login_link`: Request login link
- `POST /api/users/login_link_check`: Verify login link
- `POST /api/users/password_request`: Request password reset
- `PUT /api/users/password_reset`: Reset password with token
- `GET /api/users/me`: Get current user information
- `GET /api/users/{id}`: Get specific user (requires authentication)
- `PUT /api/users/{id}`: Update user information
- `PATCH /api/users/{id}`: Partially update user

## Security Considerations

::: warning
- Always use HTTPS in production
- Configure appropriate rate limits based on your traffic
- Set reasonable token expiration times
- Regularly purge expired tokens and inactive users
- Validate email addresses to prevent spam registrations
- Consider implementing CAPTCHA for registration and login endpoints
:::

## Troubleshooting

### Users Not Receiving Emails

Check:
1. Mailer configuration in `config/packages/mailer.yaml`
2. Environment variables for SMTP settings
3. Email logs and queue

### Rate Limiting Issues

If legitimate users are being rate-limited:
1. Adjust rate limiter settings in `framework.yaml`
2. Check cache pool configuration
3. Consider using different rate limits for different environments

### Login Links Not Working

Verify:
1. Login link handler is properly configured
2. Routes are registered correctly
3. Token lifetime is appropriate
4. Front-end URL configuration matches your application

## More Information

For detailed API documentation, refer to the auto-generated OpenAPI documentation available in your Roadiz application at `/api/docs`.
