#!/bin/bash

composer install --prefer-dist --ignore-platform-reqs && \
php artisan migrate && php artisan passport:install

# run the original entrypoint of the PHP container
exec /usr/local/bin/docker-php-entrypoint "$@"

