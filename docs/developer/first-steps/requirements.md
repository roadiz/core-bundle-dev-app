# Requirements

Roadiz is a _Symfony_ application running on PHP. 
You can follow regular [Symfony requirements](https://symfony.com/doc/6.4/setup.html#technical-requirements) to optimize your local or production setup, which includes:

- **Web Server:** Nginx or Apache with a dedicated virtual host (as described below).
- **PHP Version:** PHP 8.3+
- **PHP Extensions:** JSON, Intl, cURL, MBString, PCRE, Session, Zip, SimpleXML, and Tokenizer (usually installed by default).
- **PHP Configuration:** Ensure `php.ini` has the following settings:
    - `short_open_tag = Off`
    - `magic_quotes_gpc = Off`
    - `register_globals = Off`
    - `session.auto_start = Off`
- **Database:** MariaDB 10.11+ or MySQL 8.0+ with `JSON_*` functions support.
- **Other Tools:** [Composer](https://getcomposer.org/download/) for dependencies management, _Git_ for versioning.
- If your local environment has *ffmpeg* installed, Roadiz can use it to generate video thumbnails.

## Development workflow

Roadiz is not meant to be deployed directly to a production server out-of-the-box. It is a Symfony application that must be configured and customized in a development environment before committing your own project repository configuration and migrations. Deployment methods include SFTP, SSH, Git, or Docker. Like any Symfony app, you need to clear cache, run migrations, and perform other tasks when deploying to a new environment, which may require a Shell access or building a Docker image with a custom entrypoint script.

## Using Docker for Development and Production

Roadiz and Symfony heavily rely on [Docker](https://docs.docker.com/get-started/) and [Docker Compose](https://docs.docker.com/compose/) to ease-up development and deployment, especially using tools like *GitLab* or *GitHub Actions*. 
We recommend creating Docker images containing **all your project sources and dependencies**.

The *Roadiz Skeleton* project includes a multi-stage `Dockerfile` with PHP, Nginx, and Varnish. Feel free to customize it to suit your project needs. 
You can use `docker-bake.hcl` in your CI pipeline to build all project Docker images at once.

`docker compose` is recommended for use on the host machine, particularly for Windows and macOS users. However, 
Docker is not mandatory—you can install PHP and a web server directly on your host by following the official [Symfony setup instructions](https://symfony.com/doc/current/setup.html#technical-requirements).

### One Container per Process

Since Roadiz v2.1, we recommend separating processes into different Docker containers, allowing independent scaling. For example:

- Multiple **PHP-FPM** containers can run your application.
- A single **Nginx** container can serve static assets.
- A single **Redis** container can handle caching.

This setup enables more efficient resource allocation and scalability. See [Infrastructure section](../infrastructure/infrastructure.md) for more details on how to set up your Docker environment.

