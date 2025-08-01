# Create a New Roadiz Project

For new projects, **Roadiz** can be easily set up using the `create-project` command and our *Skeleton*.

```bash
# Create a new Roadiz project
composer create-project roadiz/skeleton my-website
cd my-website
# Create a local Dotenv to store your secrets
cp .env .env.local
# Edit your docker compose parameter in .env to fit your development environment (OS, UID).
# .env file will be tracked by Git
#
# Customize docker-compose.yml file to set your stack name
#
# Initialize your Docker environment
docker compose build
docker compose up -d --force-recreate
```

**Roadiz** and **Symfony** heavily rely on [Docker](https://docs.docker.com/get-started/) and [Docker Compose](https://docs.docker.com/compose/). If you’re not using these tools yet, we recommend learning them.

You can still use Roadiz without Docker, but you will need to manually install and configure a *PHP* environment, a *MySQL* database, and a web server. If you're not using Docker, ignore the `docker compose exec app` prefix in the following commands.


::: info
Roadiz v2 is a complete rewrite as a true *Symfony* Bundle. It behaves like a standard *Symfony* app and is intended as a headless CMS with *API Platform*. You can still use *Controllers* and *Twig* templates, but there is no more theme logic—just Symfony Bundles and your own code in the `./src` folder.
:::

When prompted by *Composer*, choose `no` for versioning history. This will replace *roadiz/skeleton*'s Git repository with your own. You can then customize all files in your project and track them using Git. A default `.gitignore` is provided to prevent committing sensitive configuration settings, ensuring different setups for development and production without merge conflicts.

## Generate JWT Private and Public Keys

When using `composer create-project`, JWT secrets and certificates should be automatically generated. If not, generate them manually:

```bash
# Generate Symfony secrets
docker compose exec app bin/console secrets:generate-keys;
# Set a random passphrase for Application secret and JWT keys
docker compose exec app bin/console secrets:set APP_SECRET --random;
docker compose exec app bin/console secrets:set JWT_PASSPHRASE --random;
# Generate your key pair
docker compose exec app bin/console lexik:jwt:generate-keypair;
```

## Install Database

```bash
# Create and migrate Roadiz database schema
docker compose exec app bin/console doctrine:migrations:migrate
# Migrate any existing data types
docker compose exec app bin/console app:install
# Install base Roadiz fixtures, default translation, and settings
docker compose exec app bin/console install
# Stop workers to force restart them
docker compose exec app php bin/console messenger:stop-workers
# Clear cache
docker compose exec app bin/console cache:clear
# Create your admin account
docker compose exec app bin/console users:create -m username@roadiz.io -b -s username
```

Then connect to `http://localhost:${YOUR_PORT}/rz-admin` to access your freshly created Roadiz back office.

::: info
If you set up [Traefik](https://doc.traefik.io/traefik/) on your local environment, you can reach your Roadiz app using `domain.test` without specifying a non-default port. Change the `HOSTNAME` variable in `.env` and update your local DNS by adding `127.0.0.1 domain.test` to your `/etc/hosts` file.
:::
