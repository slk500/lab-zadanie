#!/bin/sh
set -e

if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate --force
  php artisan jwt:secret --force
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Wait for DB to be ready
echo "Waiting for database…"
until php artisan migrate --force 2>&1; do
  echo "Migration failed, retrying in 3s…"
  sleep 3
done

# Seed only on first run
if [ ! -f storage/app/.seeded ]; then
  echo "Seeding database…"
  php artisan db:seed --force && touch storage/app/.seeded
fi

exec "$@"
