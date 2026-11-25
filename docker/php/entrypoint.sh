#!/bin/sh
set -e

# Defaults for the variables referenced from conf.d/app.ini
export APP_TIMEZONE="${APP_TIMEZONE:-Asia/Tashkent}"
export PHP_OPCACHE_VALIDATE_TIMESTAMPS="${PHP_OPCACHE_VALIDATE_TIMESTAMPS:-1}"

if [ "${APP_ENV}" = "production" ]; then
    # Configuration is only known at runtime, so caches are built on start. bootstrap/cache is
    # writable by the application and its files are PHP loaded on every boot: they are deleted
    # with the shell, not trusted from the previous run, and rebuilt from the read-only code.
    rm -f bootstrap/cache/*.php
    php artisan optimize --ansi
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --ansi
fi

exec "$@"
