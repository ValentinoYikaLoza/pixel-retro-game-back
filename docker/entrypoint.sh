#!/bin/sh
set -e

cd /var/www/html

# Instala dependencias si el volumen está vacío (primer arranque limpio).
# vendor/ es un volumen interno: comprobamos autoload.php, no el directorio
# (que ya existe como punto de montaje aunque esté vacío).
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] Instalando dependencias de Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Espera a que PostgreSQL acepte conexiones antes de migrar.
echo "[entrypoint] Esperando a PostgreSQL en ${DB_HOST}:${DB_PORT}..."
until php -r '$h=getenv("DB_HOST");$p=(int)getenv("DB_PORT");exit(@fsockopen($h,$p)?0:1);' 2>/dev/null; do
    sleep 1
done
echo "[entrypoint] PostgreSQL disponible."

php artisan config:clear
php artisan migrate --force

# Worker de cola en segundo plano: procesa los broadcasts de forma asíncrona
# para que las peticiones HTTP no paguen el coste de conexión a soketi.
echo "[entrypoint] Iniciando worker de cola..."
php artisan queue:work --tries=3 --sleep=1 --backoff=3 &

echo "[entrypoint] Servidor en http://0.0.0.0:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
