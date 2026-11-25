# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.3

# ---------------------------------------------------------------------------
# base: PHP-FPM with every extension the application needs
# ---------------------------------------------------------------------------
FROM php:${PHP_VERSION}-fpm-alpine AS base

WORKDIR /var/www/html

RUN apk add --no-cache fcgi freetype icu-libs libjpeg-turbo libpng libpq libzip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS freetype-dev icu-dev \
        libjpeg-turbo-dev libpng-dev libzip-dev linux-headers postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd intl opcache pcntl pdo_pgsql zip \
    && apk del .build-deps

COPY docker/php/conf.d/app.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/php-fpm.d/zz-healthcheck.conf /usr/local/etc/php-fpm.d/zz-healthcheck.conf
COPY --chmod=0755 docker/php/entrypoint.sh /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1

# ---------------------------------------------------------------------------
# dev: source is bind-mounted, pcov for coverage, uid/gid mapped to the host
# ---------------------------------------------------------------------------
FROM base AS dev

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers shadow \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && groupmod -o -g "${GID}" www-data \
    && usermod -o -u "${UID}" -g www-data www-data \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# No ENV APP_ENV here: an image variable would win over the APP_ENV of the bind-mounted .env.

USER www-data

# ---------------------------------------------------------------------------
# vendor: production dependencies only, resolved once and cached by layer
# ---------------------------------------------------------------------------
FROM base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist \
    --no-scripts --no-autoloader

# ---------------------------------------------------------------------------
# assets: the front-end build (Vite: Tabler, page scripts, fonts) in public/build
# ---------------------------------------------------------------------------
FROM node:24-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources/js ./resources/js
COPY resources/scss ./resources/scss
RUN npm run build

# ---------------------------------------------------------------------------
# prod: the code belongs to root and is read-only to www-data, the user php-fpm runs as; only
# storage/ and bootstrap/cache are writable, and the entrypoint rebuilds the caches in them on
# every start. Composer is not shipped. Optimised autoloader, opcache preloading off.
# ---------------------------------------------------------------------------
FROM base AS prod

# Logs go to stderr, which php-fpm passes on to `docker logs` and to a log collector: a file in the
# container's writable layer would grow without rotation and vanish with the container.
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

# storage/app holds the uploads: mount a persistent volume there. public/storage links to
# /var/www/html/storage/app/public, so a web server in front of this image needs public/ and
# storage/app/public (never storage/app/private, the patients' files) at these same paths.
# composer is mounted for this step only, so it is not in the image.
# The php base image gives /var/www/html to www-data (mode 1777): as the directory's owner it
# could still rename app/, public/ or vendor/ and plant a replacement, so it goes back to root.
RUN --mount=type=bind,from=composer:2,source=/usr/bin/composer,target=/usr/bin/composer \
    composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan package:discover --ansi \
    && mkdir -p storage/app/public storage/app/private storage/framework/cache storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
    && php artisan storage:link \
    && chown root:root /var/www/html && chmod 0755 /var/www/html \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
