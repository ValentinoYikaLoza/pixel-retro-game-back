# Imagen ligera para el API en PHP (sin Node: es un backend Laravel).
FROM php:8.2-cli

# Dependencias del sistema + extensiones PHP necesarias para PostgreSQL.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        zip \
        unzip \
        git \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip bcmath opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer (copiado desde la imagen oficial).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# OPcache: cachea bytecode en memoria (clave para el rendimiento por petición).
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Entrypoint fuera de /var/www/html para que el bind mount no lo oculte.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
