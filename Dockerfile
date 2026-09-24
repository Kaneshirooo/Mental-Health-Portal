# Used for executing npm commands
FROM node:20 AS node

# Base PHP image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Copy Node executable and npm modules from node image
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy initial Nginx config
COPY docker/nginx.conf /etc/nginx/sites-enabled/default

# Copy existing application directory contents
COPY . /var/www

# Install Composer dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Install NPM dependencies and build assets
RUN npm install
RUN npm run build

# Set permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy deployment script
COPY docker/run.sh /var/www/docker/run.sh
RUN chmod +x /var/www/docker/run.sh

# Expose port
EXPOSE 80

# Run entrypoint script
ENTRYPOINT ["/var/www/docker/run.sh"]
