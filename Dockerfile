# Qualiwuarma — imagen para Render (PHP 8.2 + Python 3.12 en el mismo contenedor)
# El servicio predictivo (Random Forest / scikit-learn) corre como subproceso
# Python invocado desde PHP (ver app/Services/PrediccionIAService.php), por eso
# ambos runtimes conviven en la misma imagen.

FROM php:8.2-cli-bookworm

# --- Dependencias del sistema (PHP exts + Python) -------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
        python3 python3-venv python3-pip \
        libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip gd mbstring xml \
    && rm -rf /var/lib/apt/lists/*

# --- Composer ---------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# --- Dependencias PHP (cacheables) ------------------------------------------
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress

# --- Entorno virtual de Python (aislado del Python del sistema) ------------
COPY python/requirements.txt ./python/requirements.txt
RUN python3 -m venv /opt/venv \
    && /opt/venv/bin/pip install --no-cache-dir -r python/requirements.txt

ENV PATH="/opt/venv/bin:${PATH}"
ENV PYTHON_BIN="/opt/venv/bin/python"

# --- Resto del código --------------------------------------------------------
COPY . .
RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app/ia_modelos \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
