FROM roadiz/php83-fpm-alpine
MAINTAINER Ambroise Maupate <ambroise@rezo-zero.com>

ARG USER_UID=1000
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=dev
ENV APP_CACHE=0
ENV APP_FFMPEG_PATH=/usr/bin/ffmpeg

RUN apk add --no-cache shadow make git ffmpeg \
    && usermod -u ${USER_UID} www-data \
    && groupmod -g ${USER_UID} www-data \
    && composer --version \
    && ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime \
    && "date" \
    && echo "USER_UID: ${USER_UID}\n" \
    && version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;")

# Display errors
ADD php.ini /usr/local/etc/php/php.ini
ADD crontab.txt /crontab.txt
ADD docker-php-entrypoint-dev /usr/local/bin/docker-php-entrypoint
ADD docker-cron-entrypoint-dev /usr/local/bin/docker-cron-entrypoint

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html/

RUN ln -s /var/www/html/bin/console /usr/local/bin/console \
    && /usr/bin/crontab -u www-data /crontab.txt \
    && chmod +x /usr/local/bin/docker-php-entrypoint \
    && chmod +x /usr/local/bin/docker-cron-entrypoint \
    && chown -R www-data:www-data /var/www/html/

USER www-data

VOLUME /var/www/html
