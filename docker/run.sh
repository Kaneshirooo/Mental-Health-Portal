#!/bin/sh

# Clear any stale cached config from the Docker build layer
# (important so Render's runtime env vars like MAIL_* are picked up)
php artisan config:clear

# Re-cache with the actual runtime environment variables
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (Optional depending on your preference, but highly recommended)
php artisan migrate --force

# Start php-fpm in background
php-fpm -D

# Start nginx in foreground
nginx -g "daemon off;"
