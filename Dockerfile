ARG UID=1000
ARG GID=${UID}

ARG PHP_VERSION=8.4.14
ARG MYSQL_VERSION=8.4.7
ARG NGINX_VERSION=1.28.0
ARG MARIADB_VERSION=11.8.3
ARG VARNISH_VERSION=7.7.3
ARG COMPOSER_VERSION=2.8.1
ARG NODE_VERSION=22.19.0
ARG PHP_EXTENSION_REDIS_VERSION=6.1.0

###########
# MySQL   #
###########

FROM mysql:${MYSQL_VERSION} AS mysql

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

ARG UID
ARG GID

COPY --link --chmod=644 docker/mysql/performances.cnf /etc/mysql/conf.d/performances.cnf

RUN <<EOF
usermod -u ${UID} mysql
groupmod -g ${GID} mysql
EOF


#############
# MariaDB   #
#############

FROM mariadb:${MARIADB_VERSION} AS mariadb

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

ARG UID
ARG GID

# https://hub.docker.com/_/mariadb
# Using a custom MariaDB configuration file
# Custom configuration files should end in .cnf and be mounted read only at the directory /etc/mysql/conf.d
COPY --link --chmod=644 docker/mariadb/performances.cnf /etc/mysql/conf.d/performances.cnf

RUN <<EOF
usermod -u ${UID} mysql
groupmod -g ${GID} mysql
EOF

###########
# Varnish #
###########

FROM varnish:${VARNISH_VERSION} AS varnish

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

COPY --link --chmod=644 docker/varnish/default.vcl /etc/varnish/

#######
# PHP #
#######

FROM php:${PHP_VERSION}-fpm-bookworm AS php

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

ARG UID
ARG GID
ARG COMPOSER_VERSION
ARG PHP_EXTENSION_REDIS_VERSION

SHELL ["/bin/bash", "-e", "-o", "pipefail", "-c"]

ENV APP_FFMPEG_PATH=/usr/bin/ffmpeg

RUN <<EOF
apt-get --quiet update
apt-get --quiet --yes --purge --autoremove upgrade
# Packages - System
apt-get --quiet --yes --no-install-recommends --verbose-versions install \
    less \
    sudo \
    ffmpeg
rm -rf /var/lib/apt/lists/*

# User
addgroup --gid ${UID} php
adduser --home /home/php --shell /bin/bash --uid ${GID} --gecos php --ingroup php --disabled-password php
echo "php ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/php

# App
install --verbose --owner php --group php --mode 0755 --directory /app

# Php extensions
curl -sSLf  https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    --output /usr/local/bin/install-php-extensions
chmod +x /usr/local/bin/install-php-extensions
install-php-extensions \
    @composer-${COMPOSER_VERSION} \
    fileinfo \
    gd \
    imagick \
    iconv \
    intl \
    json \
    mbstring \
    opcache \
    openssl \
    pcntl \
    pdo_mysql \
    simplexml \
    xsl \
    zip \
    redis-${PHP_EXTENSION_REDIS_VERSION}
EOF

WORKDIR /app

####################
# PHP - FRANKENPHP #
####################

FROM dunglas/frankenphp:php${PHP_VERSION}-bookworm AS php-franken

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com, eliot@rezo-zero.com"

ARG UID
ARG GID
ARG COMPOSER_VERSION
ARG PHP_EXTENSION_REDIS_VERSION

SHELL ["/bin/bash", "-e", "-o", "pipefail", "-c"]

ENV SERVER_NAME=":80"
ENV APP_FFMPEG_PATH=/usr/bin/ffmpeg
ENV SERVER_ROOT="/app/public"

RUN <<EOF
apt-get --quiet update
apt-get --quiet --yes --purge --autoremove upgrade
# Packages - System
apt-get --quiet --yes --no-install-recommends --verbose-versions install \
    acl \
    less \
    sudo \
    git \
    ffmpeg
rm -rf /var/lib/apt/lists/*

# User
addgroup --gid ${UID} php
adduser --home /home/php --shell /bin/bash --uid ${GID} --gecos php --ingroup php --disabled-password php
echo "php ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/php

# App
install --verbose --owner php --group php --mode 0755 --directory /app

# Php extensions
install-php-extensions \
    @composer-${COMPOSER_VERSION} \
    fileinfo \
    gd \
    imagick \
    iconv \
    intl \
    json \
    mbstring \
    opcache \
    openssl \
    pcntl \
    pdo_mysql \
    simplexml \
    xsl \
    zip \
    redis-${PHP_EXTENSION_REDIS_VERSION}

setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp

chown --recursive ${UID}:${GID} /data/caddy /config/caddy

EOF

ENTRYPOINT ["docker-php-entrypoint"]

WORKDIR /app

#######################
# Php - franken - Dev #
#######################

FROM php-franken AS php-dev-franken

ENV XDEBUG_MODE=off

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY --link --chmod=755 docker/frankenphp/docker-php-entrypoint-dev /usr/local/bin/docker-php-entrypoint
COPY --link docker/frankenphp/conf.d/app.dev.ini ${PHP_INI_DIR}/conf.d/zz-app.ini
COPY --link docker/frankenphp/Caddyfile.dev /etc/frankenphp/Caddyfile

CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]

USER php

VOLUME /app

#######################
# Php - franken - Prod #
#######################

FROM php-franken AS php-prod-franken

ENV XDEBUG_MODE=off

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link --chmod=755 docker/frankenphp/docker-php-entrypoint-dev /usr/local/bin/docker-php-entrypoint
COPY --link docker/frankenphp/conf.d/app.prod.ini ${PHP_INI_DIR}/conf.d/zz-app.ini
COPY --link docker/frankenphp/Caddyfile /etc/frankenphp/Caddyfile

CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]

USER php

VOLUME /app

#############
# Php - Dev #
#############

FROM php AS php-dev

# Configs
RUN ln -sf ${PHP_INI_DIR}/php.ini-development ${PHP_INI_DIR}/php.ini
COPY --link docker/php/conf.d/app.dev.ini   ${PHP_INI_DIR}/conf.d/zz-app.ini
COPY --link docker/php/fpm.d/www.dev.conf   ${PHP_INI_DIR}-fpm.d/zz-www.conf

# Entrypoint
COPY --link --chmod=755 docker/php/docker-entrypoint.dev /usr/local/bin/docker-entrypoint
ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

USER php

#########
# Nginx #
#########

FROM nginx:${NGINX_VERSION}-bookworm AS nginx

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

ARG UID
ARG GID

SHELL ["/bin/bash", "-e", "-o", "pipefail", "-c"]

RUN <<EOF
# Packages
apt-get --quiet update
apt-get --quiet --yes --purge --autoremove upgrade
apt-get --quiet --yes --no-install-recommends --verbose-versions install \
    less \
    sudo
rm -rf /var/lib/apt/lists/*

# User
groupmod --gid ${GID} nginx
usermod --uid ${UID} nginx
echo "nginx ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/nginx

# App
install --verbose --owner nginx --group nginx --mode 0755 --directory /app
EOF

WORKDIR /app

###############
# Nginx - Dev #
###############

FROM nginx AS nginx-dev

# Config
COPY --link docker/nginx/nginx.conf              /etc/nginx/nginx.conf
COPY --link docker/nginx/conf.d/_gzip.conf       /etc/nginx/conf.d/_gzip.conf
COPY --link docker/nginx/conf.d/_security.conf   /etc/nginx/conf.d/_security.conf
COPY --link docker/nginx/conf.d/default.dev.conf /etc/nginx/conf.d/default.conf

#############
# Node      #
#############

FROM node:${NODE_VERSION}-bookworm-slim AS node

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com eliot@rezo-zero.com"

ARG UID
ARG GID

# Fix: "FATAL ERROR: Reached heap limit Allocation failed - JavaScript heap out of memory"
ENV NODE_OPTIONS="--max_old_space_size=4096"

# Prevent Corepack pnpm download confirm prompt
ENV COREPACK_ENABLE_DOWNLOAD_PROMPT=0

SHELL ["/bin/bash", "-e", "-o", "pipefail", "-c"]

RUN <<EOF
apt-get --quiet update
apt-get --quiet --yes --purge --autoremove upgrade

# Packages - System
# GIT is required for Vitepress
apt-get --quiet --yes --no-install-recommends --verbose-versions install \
    curl \
    less \
    git \
    sudo
rm -rf /var/lib/apt/lists/*

# User
groupmod --gid ${GID} node
usermod --uid ${UID} node
chown --verbose --recursive node:node /home/node
echo "node ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/node

# App
install --verbose --owner node --group node --mode 0755 --directory /app

# https://github.com/pnpm/pnpm/issues/9029
npm i -g corepack@latest

# Pnpm
corepack enable pnpm
EOF

WORKDIR /app

###############
# Node - Dev  #
###############

FROM node AS node-dev

ARG UID
ARG GID

USER node

EXPOSE 5173

COPY --link --chown=${UID}:${GID} lib/Rozier/package.json lib/Rozier/pnpm-lock.yaml ./
RUN <<EOF
# Pnpm
corepack enable pnpm

pnpm install --config.platform=linux --config.architecture=x64
EOF

CMD ["pnpm", "dev", "--host", "0.0.0.0"]


####################
# Vitepress - Dev  #
####################

FROM node AS vitepress-dev

ARG UID
ARG GID

USER node

EXPOSE 5174

COPY --link --chown=${UID}:${GID} docs/package.json docs/pnpm-lock.yaml docs/pnpm-workspace.yaml ./

RUN <<EOF
# Pnpm
corepack enable pnpm

pnpm install --config.platform=linux --config.architecture=x64
EOF

CMD ["pnpm", "docs:dev", "--port", "5174", "--strictPort 1", "--host", "0.0.0.0"]
