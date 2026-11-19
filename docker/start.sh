#!/bin/sh
# Container start: listen on $PORT, prepare SQLite, import fonts, demo accounts.
set -e
PORT="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

DB="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
[ -f "$DB" ] || touch "$DB"
chown www-data:www-data "$DB"

php artisan migrate --force
php artisan fonts:sync --skip-build
php artisan demo:accounts
if [ -n "$ADMIN_EMAIL" ]; then php artisan admin:make "$ADMIN_EMAIL"; fi
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache database

exec apache2-foreground
