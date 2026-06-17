#!/bin/bash
set -e

cd /var/www/html

# Create .env from environment variables if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Override with environment variables
sed -i "s|APP_ENV=.*|APP_ENV=production|g" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|g" .env
sed -i "s|APP_URL=.*|APP_URL=${APP_URL:-http://localhost}|g" .env
sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=file|g" .env
sed -i "s|CACHE_STORE=.*|CACHE_STORE=file|g" .env
sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|g" .env

# Set APP_KEY if provided via environment
if [ -n "$APP_KEY" ]; then
    sed -i "s|APP_KEY=.*|APP_KEY=${APP_KEY}|g" .env
fi

# Generate key if empty
php artisan key:generate --force 2>/dev/null || true

# Ensure sqlite db exists
touch database/database.sqlite

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache database

# Run migrations
php artisan migrate --force

# Cache
php artisan config:cache
php artisan route:cache

# Start Apache
apache2-foreground
