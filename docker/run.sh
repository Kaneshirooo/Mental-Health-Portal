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

# Create the public/storage symlink so uploaded profile pictures are actually
# served. Without this, uploads land in storage/app/public and 404.
php artisan storage:link
mkdir -p storage/app/public/profiles
chown -R www-data:www-data storage/app/public

# Start php-fpm in background
php-fpm -D

# Start nginx in foreground
nginx -g "daemon off;"
