# Configuration

Roadiz is a full-stack Symfony application. It follows its configuration scheme as described in
[Symfony Configuration](https://symfony.com/doc/6.4/configuration.html).

## Choose your database inheritance model

*Roadiz*'s main feature is its polymorphic data model, mapped to a relational database. 
This structure can cause performance bottlenecks when dealing with more than 20-30 node types. 
To address this, we made the data inheritance model configurable.

Roadiz defaults to [`single_table`](https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/reference/inheritance-mapping.html#single-table-inheritance) mode for better performances with many node-types. 
However, this model does not support fields with the *same name but different types* since all node-type fields are stored in the **same SQL table**.
Single table inheritance is the best option when you fetch different node-sources as the same time (for page blocks).

::: tip
In `single_table` mode, you can optimize your performances but reusing field `name` across node-types whenever possible.
This will keep your columns count low and your queries fast.
:::

For mixed field types, you can switch to [`joined`](https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/reference/inheritance-mapping.html#class-table-inheritance) inheritance type. 
This model is better with a small number of node-types (max. 20) but with very different fields. It requires *LEFT JOIN* operations 
on each node-source query unless a node-type criterion is specified.
Joined table inheritance is a better option when you always fetch node-sources of the same type (for example, a list of articles).

Configure *Doctrine* strategy in `config/packages/roadiz_core.yaml`:

```yaml
roadiz_core:
    inheritance:
        # type: joined
        type: single_table
```

- **Joined class inheritance**: `joined`
- **Single table inheritance**: `single_table`

::: warning
Changing this setting after content creation will **erase all node-source data**.
:::

## Apache Solr endpoint

Roadiz uses *Apache Solr* as Search engine and for indexing nodes-sources. Configure Solr in `config/packages/roadiz_core.yaml`:

```yaml
roadiz_core:
    solr:
        endpoint:
            localhost:
                host: "localhost"
                port: "8983"
                path: "/"
                core: "mycore"
                timeout: 3
                username: ""
                password: ""
```

Run the following command to check your Solr index:

```sh
./bin/console solr:check
```

## Reverse Proxy Cache Invalidation

Roadiz supports cache invalidation for both external (e.g., *Varnish*) and internal (*Symfony* AppCache) reverse proxies. 
When the back-office cache is cleared, Roadiz sends a `BAN` request. For node-source updates, a `PURGE` request is sent using the first reachable node-source URL.

### Varnish Configuration

```yaml
roadiz_core:
    reverseProxyCache:
        frontend:
            default:
                host: '%env(string:VARNISH_HOST)%'
                domainName: '%env(string:VARNISH_DOMAIN)%'
```

To ensure proper cache handling, configure your external reverse proxy:
[Reverse Proxy Configuration](https://github.com/roadiz/roadiz/blob/develop/samples/varnish_default.vcl).

### API Platform Invalidation

For API Platform, configure `http_cache`:

```yaml
api_platform:
    http_cache:
        invalidation:
            enabled: true
            varnish_urls: ['%env(VARNISH_URL)%']
```

### Cloudflare Proxy Cache

Roadiz can send purge requests to Cloudflare. Collect the following information:

- Cloudflare zone identifier
- Cloudflare API credentials (Bearer token or email + auth-key)

**Using Bearer token:**

```yaml
roadiz_core:
    reverseProxyCache:
        frontend: []
        cloudflare:
            zone: cloudflare-zone
            bearer: ~
```

**Using Email and AuthKey:**

```yaml
roadiz_core:
    reverseProxyCache:
        frontend: []
        cloudflare:
            zone: cloudflare-zone
            email: ~
            key: ~
```

## Entities Paths

Roadiz uses *Doctrine* to map object entities to database tables. Example configuration:

```yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
    orm:
        default_entity_manager: default
        entity_managers:
            default:
                mappings:
                    App:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/src/Entity'
                        prefix: 'App\Entity'
                        alias: App
```

::: tip
Use `type: attribute` whenever possible. Doctrine annotations are deprecated.
:::

## Configure Mailer

Roadiz uses *Symfony Mailer* for email handling.
[Symfony Mailer Setup](https://symfony.com/doc/6.4/mailer.html#transport-setup)

::: warning
Ensure your email sender is from a validated domain to avoid being blacklisted.
:::

## Image Processing

Roadiz integrates with [Intervention Request Bundle](https://github.com/rezozero/intervention-request-bundle) for automatic image resizing and optimization.

```yaml
rz_intervention_request:
    driver: '%env(IR_DRIVER)%'
    default_quality: '%env(int:IR_DEFAULT_QUALITY)%'
    max_pixel_size: '%env(int:IR_MAX_PIXEL_SIZE)%'
    cache_path: "%kernel.project_dir%/public/assets"
    files_path: "%kernel.project_dir%/public/files"
    jpegoptim_path: /usr/bin/jpegoptim
    pngquant_path: /usr/bin/pngquant
    subscribers: []
```

### Kraken.io Integration

```yaml
rz_intervention_request:
    subscribers:
        - class: "AM\\InterventionRequest\\Listener\\KrakenListener"
          args:
               - "your-api-key"
               - "your-api-secret"
               - true
```

::: warning
Each generated image is sent to *kraken.io*, which may increase loading times.
:::

## Two-Factor Authentication

To enable Two-Factor Authentication (2FA), install the package:

```sh
composer require roadiz/two-factor-bundle
```

Configure in `config/packages/scheb_2fa.yaml` and `config/packages/security.yaml`.

[Two-Factor Authentication Docs](https://github.com/roadiz/two-factor-bundle#configuration)

## OpenID SSO Authentication

Roadiz supports OpenID authentication with Google accounts. Configuration options:

```yaml
roadiz_rozier:
    open_id:
        verify_user_info: false
        discovery_url: '%env(string:OPEN_ID_DISCOVERY_URL)%'
        hosted_domain: '%env(string:OPEN_ID_HOSTED_DOMAIN)%'
        oauth_client_id: '%env(string:OPEN_ID_CLIENT_ID)%'
        oauth_client_secret: '%env(string:OPEN_ID_CLIENT_SECRET)%'
        requires_local_user: false
        granted_roles:
            - ROLE_USER
            - ROLE_BACKEND_USER
```

### Authentik SSO

[Authentik documentation](https://docs.goauthentik.io/docs/)

#### Declare Variables in Your `.env`

Set the following environment variables in your `.env.local` file:

- `PG_PASS` (Auth0 recommends generating this with OpenSSL)
- `AUTHENTIK_SECRET_KEY` (Auth0 recommends generating this with OpenSSL)
- `PG_USER` (PostgreSQL user)
- `PG_DB` (PostgreSQL database)

#### Running Authentik

Start your authentik application with :

```shell
 docker compose -f compose.authentik.yml --env-file .env.local up -d --force-recreate
```

And navigate to:

```
http://<your-server-IP-or-hostname>:9000/if/flow/initial-setup/
```

to create your admin user in Authentik.

#### Creating an Application

Follow these steps to create an application in Authentik:

![Step 1](./img/img_create_application_1.webp)
![Step 2](./img/img_create_application_2.webp)
![Step 3](./img/img_create_application_3.webp)
![Step 4](./img/img_create_application_4.webp)

#### Configuring the Provider

Go to the provider you created:

![Provider](./img/img_application_provider.webp)

Add a URL in the redirect URL field (`.../rz-admin/login`).

![Provider](./img/img_redirect_url.webp)

#### Updating Environment Variables

Add the following variables to your `.env` file, using the data obtained from the provider in Authentik:

- `OPEN_ID_DISCOVERY_URL=<OpenID Configuration URL>`
- `OPEN_ID_CLIENT_ID=<Client ID>`
- `OPEN_ID_CLIENT_SECRET=<Client Secret>`

#### Creating a User in Authentik

From the Authentik admin panel, create a user with a scope similar to Roadiz:

![Provider](./img/img_application_user.webp)

#### Logging into Roadiz with OpenID

Now, when you go to the Roadiz login page:

![Provider](./img/img_openid.webp)

You can log in using OpenID. The login button has been updated to indicate connection via Authentik.
Clicking it will redirect you to the Authentik login page, and after authentication, you will be automatically redirected to the Roadiz back office.

## Console Commands

Run Roadiz via CLI:

```sh
bin/console
```

If *php* is not in `/usr/bin/php`, use:

```sh
php bin/console
```

To view available commands:

```sh
bin/console --help
```

For Doctrine tools:

```sh
bin/console doctrine:migrations:migrate
```

::: warning
Always backup your database before running Doctrine operations.
:::

