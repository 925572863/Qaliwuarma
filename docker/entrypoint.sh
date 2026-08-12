#!/bin/sh
# Punto de entrada del contenedor en Render.
# Corre migraciones (idempotente, seguro en cada deploy) y arranca el
# servidor embebido de Laravel en el puerto que Render inyecta via $PORT.
set -e

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
