#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/linkdrop"

echo "==> Pulling latest code…"
cd "$APP_DIR"
git pull origin main

echo "==> Installing Composer dependencies…"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Installing & building frontend…"
npm ci --omit=dev
npm run build

echo "==> Running migrations…"
php artisan migrate --force

echo "==> Caching config, routes, views…"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Reloading PHP-FPM…"
sudo systemctl reload php8.2-fpm

echo "==> Deploy complete."
