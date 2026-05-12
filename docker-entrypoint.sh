#!/bin/sh
set -e

# ── Port dynamique (Render injecte $PORT à runtime) ─────────────────────────
export SERVER_NAME=":${PORT:-8080}"

echo "╔══════════════════════════════════════════╗"
echo "║     ENSIAS Hub Backend — Starting...     ║"
echo "║     Server: ${SERVER_NAME}               ║"
echo "╚══════════════════════════════════════════╝"

# ── Cache config & routes (perf) ────────────────────────────────────────────
php artisan config:cache
php artisan route:cache

# ── Migrations (idempotent — sûr à rejouer) ──────────────────────────────────
php artisan migrate --force

# ── Démarrer FrankenPHP ───────────────────────────────────────────────────────
exec frankenphp run --config /etc/caddy/Caddyfile --adapter caddyfile
