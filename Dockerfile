# ── ENSIAS Hub Backend — FrankenPHP + Laravel Octane ──────────────────────────
FROM dunglas/frankenphp:latest-php8.2-alpine

LABEL maintainer="ENSIAS Hub PFA"

# ── PHP extensions requis par Laravel et PostgreSQL ───────────────────────────
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    mbstring \
    bcmath \
    zip \
    gd \
    intl \
    opcache \
    pcntl \
    exif

# ── Composer ──────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# ── Dépendances PHP ───────────────────────────────────────────────────────────
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

# ── Code source ───────────────────────────────────────────────────────────────
COPY . .

# ── Permissions storage & cache ───────────────────────────────────────────────
RUN mkdir -p storage/logs \
             storage/framework/cache \
             storage/framework/sessions \
             storage/framework/views \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ── Nettoyage des caches au démarrage et lancement de Laravel Octane ──────────
# C'est LA ligne magique pour Render : on utilise le port dynamique fourni par Render
CMD php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=${PORT:-10000}