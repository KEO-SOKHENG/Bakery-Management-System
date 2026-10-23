#!/bin/sh
set -e

# Support Render dynamic port assignment (defaults to 80 if not set)
PORT="${PORT:-80}"

# Adjust Apache port in ports.conf and 000-default.conf
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure required storage and cache directories exist
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         storage/app/public \
         bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Safe read-only diagnostic check of database connectivity (non-destructive, credentials never logged)
if php artisan db:diagnostic; then
    # Optional: Run database migrations only if explicitly enabled AND connectivity verified
    if [ "${RUN_MIGRATIONS}" = "true" ]; then
        echo "RUN_MIGRATIONS=true detected. Running migrations..."
        php artisan migrate --force --no-interaction
    fi
else
    echo "Database connectivity diagnostic check failed. Please verify database configuration."
    if [ "${RUN_MIGRATIONS}" = "true" ]; then
        echo "Aborting migrations due to database connectivity failure."
        exit 1
    fi
fi

# Execute CMD (apache2-foreground)
exec "$@"
