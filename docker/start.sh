#!/bin/bash
set -e

cd /var/www/html

# Create .env from environment variables if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Override with environment variables
sed -i "s|APP_ENV=.*|APP_ENV=production|g" .env
sed -i "s|APP_URL=.*|APP_URL=https://qaliwuarma.onrender.com|g" .env
echo "FORCE_HTTPS=true" >> .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|g" .env
sed -i "s|APP_URL=.*|APP_URL=${APP_URL:-https://qaliwuarma.onrender.com}|g" .env
sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=file|g" .env
sed -i "s|CACHE_STORE=.*|CACHE_STORE=file|g" .env
sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|g" .env
sed -i "s|SESSION_DOMAIN=.*|SESSION_DOMAIN=null|g" .env

# Set fixed APP_KEY
sed -i "s|APP_KEY=.*|APP_KEY=base64:oXHr5gyDsE4LV38ue6AkK/leDC464GTIzv/U8zccOTw=|g" .env

# Ensure sqlite db exists
touch database/database.sqlite

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache database

# Run migrations
php artisan migrate --force

# Clear old cache and rebuild
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

# Start Apache
apache2-foreground
