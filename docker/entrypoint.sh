#!/bin/sh
set -e
cd /var/www/html

if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY belum diisi. Buat dengan: php artisan key:generate --show" >&2
  exit 1
fi

# Link storage publik (aman dijalankan berulang)
php artisan storage:link --force >/dev/null 2>&1 || true

# Migrasi otomatis bila RUN_MIGRATIONS=true
if [ "$RUN_MIGRATIONS" = "true" ]; then
  php artisan migrate --force
fi

# Cache konfigurasi untuk production
if [ "$APP_ENV" = "production" ]; then
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

exec "$@"
