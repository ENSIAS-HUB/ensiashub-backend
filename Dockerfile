# ── ENSIAS Hub Backend — Serveur Natif (100% Fiable pour Render) ──────────
FROM php:8.2-cli

# ── Installation des extensions PHP sans erreur ───────────────────────────
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_pgsql pgsql mbstring bcmath zip gd intl opcache pcntl exif

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

# ── Lancement infaillible avec le port dynamique de Render ────────────────────
CMD ["sh", "-c", "php artisan config:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]