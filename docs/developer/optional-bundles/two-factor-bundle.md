# Two-Factor Authentication Bundle

The Roadiz Two-Factor Authentication Bundle provides enhanced security for your Roadiz application by implementing two-factor authentication (2FA). It's based on the [scheb/two-factor-bundle](https://github.com/scheb/2fa).

## Features

- **OTP Authentication**: One-Time Password authentication with Google Authenticator
- **Backup Codes**: Hashed and securely stored backup codes for account recovery
- **Trusted Devices**: Remember devices for a configurable amount of time
- **Environment Integration**: Uses `APP_NAMESPACE`, `APP_TITLE`, and `APP_SECRET` environment variables

## Installation

Install the bundle using Composer:

```bash
composer require roadiz/two-factor-bundle
```

If you're not using Symfony Flex, you'll need to manually enable the bundle in `config/bundles.php`:

```php
// config/bundles.php

return [
    // ...
    \RZ\Roadiz\TwoFactorBundle\RoadizTwoFactorBundle::class => ['all' => true],
];
```

## Configuration

### Step 1: Copy Configuration Files

Copy the scheb 2FA configuration from the bundle to your project:

```bash
cp vendor/roadiz/two-factor-bundle/config/packages/scheb_2fa.yaml config/packages/
```

::: tip
You can customize the 2FA settings in this file according to your security requirements.
:::

### Step 2: Add Routes

Add the bundle routes to your project's `config/routes.yaml`:

```yaml
# config/routes.yaml
roadiz_two_factor:
    resource: "@RoadizTwoFactorBundle/config/routing.yaml"
```

### Step 3: Configure Environment Variables

Ensure the following environment variables are set in your `.env` file:

```dotenv
APP_NAMESPACE=your_app_namespace
APP_TITLE="Your Application Title"
APP_SECRET=your_secure_secret_key
```

These variables are used for generating QR codes and configuring the authenticator app.

## Usage

### Enabling 2FA for Users

Once the bundle is installed and configured, users can enable two-factor authentication from their account settings in the Roadiz back office (Rozier).

### Authentication Flow

1. User enters their username and password
2. If 2FA is enabled, they are prompted for their OTP code
3. User enters the code from their authenticator app (e.g., Google Authenticator)
4. Upon successful verification, the user is logged in

### Backup Codes

When enabling 2FA, users receive backup codes that can be used to access their account if they lose access to their authenticator device. These codes are:
- Hashed and securely stored in the database
- Single-use only
- Should be stored in a safe place by the user

### Trusted Devices

Users can mark devices as "trusted" to skip 2FA for a configured period. This improves the user experience while maintaining security for untrusted devices.

## Customization

### Customizing the 2FA Configuration

Edit the `config/packages/scheb_2fa.yaml` file to customize various aspects:

```yaml
# config/packages/scheb_2fa.yaml
scheb_two_factor:
    # Configure trusted device settings
    trusted_device:
        enabled: true
        lifetime: 2592000 # 30 days in seconds
        
    # Configure backup code settings
    backup_codes:
        enabled: true
        
    # Configure Google Authenticator settings
    google:
        enabled: true
        server_name: '%env(APP_TITLE)%'
        issuer: '%env(APP_NAMESPACE)%'
```

### Security Considerations

::: warning
- Always use strong, unique values for `APP_SECRET`
- Store backup codes securely
- Consider the security implications of trusted device lifetime
- Regularly review and update your 2FA settings
:::

## Troubleshooting

### QR Code Not Displaying

If the QR code for Google Authenticator is not displaying:
1. Ensure `APP_NAMESPACE` and `APP_TITLE` are properly configured
2. Check that the bundle routes are correctly registered
3. Clear your application cache

### Users Locked Out

If a user loses access to their authenticator:
1. They can use their backup codes to log in
2. As an administrator, you can disable 2FA for the user from the back office.
3. The user can then re-enable 2FA with a new authenticator device

## More Information

For more details on the underlying implementation, refer to the [scheb/two-factor-bundle documentation](https://symfony.com/bundles/SchebTwoFactorBundle/current/index.html).
