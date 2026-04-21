#!/bin/sh
set -e

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Wait for DB to be ready
echo "Waiting for database…"
until php artisan migrate --force 2>&1; do
  echo "Migration failed, retrying in 3s…"
  sleep 3
done

exec "$@"
