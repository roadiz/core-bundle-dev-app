ARG UID=1000
ARG GID=${UID}

ARG PHP_VERSION=8.4.1
ARG VARNISH_VERSION=7.6.1
ARG NGINX_VERSION=1.27.2
ARG MYSQL_VERSION=8.0.40
ARG MARIADB_VERSION=10.11.9
ARG SOLR_VERSION=9


##########
# Solr   #
##########

FROM solr:${SOLR_VERSION}-slim AS solr

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

ARG UID
ARG GID

USER root

RUN <<EOF
set -ex
usermod -u ${UID} "$SOLR_USER"
groupmod -g ${GID} "$SOLR_GROUP"
chown -R ${UID}:${GID} /var/solr
EOF

COPY docker/solr/managed-schema.xml /opt/solr/server/solr/configsets/_default/conf/managed-schema

USER $SOLR_USER


###########
# MySQL   #
###########

FROM mysql:${MYSQL_VERSION} AS mysql

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

ARG UID
ARG GID

COPY --link docker/mysql/performances.cnf /etc/mysql/conf.d/performances.cnf

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

COPY --link docker/mysql/performances.cnf /etc/mysql/conf.d/performances.cnf

RUN <<EOF
usermod -u ${UID} mysql
groupmod -g ${GID} mysql
EOF

###########
# Varnish #
###########

FROM varnish:${VARNISH_VERSION} AS varnish

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

COPY --link docker/varnish/default.vcl /etc/varnish/

#######
# PHP #
#######

FROM php:${PHP_VERSION}-fpm-bookworm AS php

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

ARG UID
ARG GID

ARG COMPOSER_VERSION=2.8.2
ARG PHP_EXTENSION_INSTALLER_VERSION=2.6.0
ARG PHP_EXTENSION_REDIS_VERSION=6.1.0

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

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

ARG UID
ARG GID

ARG COMPOSER_VERSION=2.8.1
ARG PHP_EXTENSION_INSTALLER_VERSION=2.6.0
ARG PHP_EXTENSION_REDIS_VERSION=6.1.0

SHELL ["/bin/bash", "-e", "-o", "pipefail", "-c"]

ENV APP_FFMPEG_PATH=/usr/bin/ffmpeg
ENV APP_RUNTIME=Runtime\\FrankenPhpSymfony\\Runtime
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

RUN <<EOF
apt-get --quiet update
apt-get --quiet --yes --purge --autoremove upgrade
# Packages - System
apt-get --quiet --yes --no-install-recommends --verbose-versions install \
    acl \
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

COPY --link docker/frankenphp/conf.d/app.ini ${PHP_INI_DIR}/conf.d/
COPY --link --chmod=755 docker/frankenphp/docker-entrypoint.dev /usr/local/bin/docker-entrypoint
COPY --link docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

WORKDIR /app

#######################
# Php - franken - Dev #
#######################

FROM php-franken AS php-dev-franken

ENV XDEBUG_MODE=off

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY --link docker/frankenphp/conf.d/app.dev.ini ${PHP_INI_DIR}/conf.d/

CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--watch" ]

USER php

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

##############
# Cron - Dev #
##############

FROM php-dev AS cron-dev

# Need to go back with root user
USER root

COPY --link docker/cron/crontab.txt /crontab.txt

RUN <<EOF
# Packages
apt-get --quiet update
apt-get --quiet --yes --no-install-recommends --verbose-versions install cron
rm -rf /var/lib/apt/lists/*
/usr/bin/crontab -u php /crontab.txt
EOF

# Entrypoint
COPY --link --chmod=755 docker/cron/docker-cron-entrypoint.dev /usr/local/bin/docker-entrypoint
ENTRYPOINT ["docker-entrypoint"]

USER root

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
