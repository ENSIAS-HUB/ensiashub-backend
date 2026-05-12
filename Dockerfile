# ── ENSIAS Hub Backend — FrankenPHP (Caddy + PHP 8.2) ──────────────────────────
FROM dunglas/frankenphp:latest-php8.2-alpine

LABEL maintainer="ENSIAS Hub PFA"

# ── PHP extensions requis par Laravel ────────────────────────────────────────
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

# ── Dépendances PHP (layer mis en cache si composer.json non modifié) ─────────
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --optimize-autoloader

# ── Code source ───────────────────────────────────────────────────────────────
COPY . .

# ── Post-install (génère autoload etc.) ──────────────────────────────────────
RUN composer run-script post-autoload-dump 2>/dev/null || true

# ── Permissions storage & cache ───────────────────────────────────────────────
RUN mkdir -p storage/logs \
              storage/framework/cache \
              storage/framework/sessions \
              storage/framework/views \
              bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ── Config Caddy & entrypoint ─────────────────────────────────────────────────
COPY Caddyfile /etc/caddy/Caddyfile
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
