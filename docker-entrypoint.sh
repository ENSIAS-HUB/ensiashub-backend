#!/bin/sh
set -e

echo "Starting Laravel deployment..."

echo "Current DB connection: ${DB_CONNECTION:-not_set}"
echo "Current cache store: ${CACHE_STORE:-not_set}"

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan event:clear || true

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting FrankenPHP on port ${PORT:-10000}..."

exec php artisan octane:frankenphp \
    --host=0.0.0.0 \
    --port="${PORT:-10000}" \
    --workers=auto \
    --max-requests=500
