#!/bin/sh
set -e

echo "Starting Laravel deployment..."

php artisan config:clear
php artisan cache:clear

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting FrankenPHP on port ${PORT:-10000}..."

exec php artisan octane:start \
    --server=frankenphp \
    --host=0.0.0.0 \
    --port="${PORT:-10000}" \
    --workers=auto \
    --max-requests=500
