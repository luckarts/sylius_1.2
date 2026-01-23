#!/bin/sh
set -e

# Fix permissions for var directory (cache, logs, sessions)
if [ -d /var/www/html/var ]; then
    mkdir -p /var/www/html/var/cache/dev/profiler \
             /var/www/html/var/logs \
             /var/www/html/var/sessions
    chmod -R 777 /var/www/html/var
fi

# Fix permissions for media directory (uploads)
if [ -d /var/www/html/web/media ]; then
    chown -R www-data:www-data /var/www/html/web/media
    chmod -R 775 /var/www/html/web/media
fi

# Execute the main command
exec "$@"