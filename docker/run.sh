#!/bin/sh

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (Optional depending on your preference, but highly recommended)
php artisan migrate --force

# Start php-fpm in background
php-fpm -D

# Start nginx in foreground
nginx -g "daemon off;"
