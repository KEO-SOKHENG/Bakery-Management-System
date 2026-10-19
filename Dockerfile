# Stage 1: Build frontend assets
FROM node:24-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build


# Stage 2: Production PHP runtime
FROM php:8.3-apache

# Install system dependencies, PostgreSQL driver, and GD for DomPDF
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pgsql bcmath intl zip gd \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application source (excluding files listed in .dockerignore)
COPY . .

# Install PHP production dependencies
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Copy compiled frontend assets from assets stage
COPY --from=assets /app/public/build /var/www/html/public/build

# Configure Apache virtual host for Laravel
RUN printf '%s\n' \
    '<VirtualHost *:80>' \
    '    DocumentRoot /var/www/html/public' \
    '    <Directory /var/www/html/public>' \
    '        AllowOverride All' \
    '        Require all granted' \
    '    </Directory>' \
    '</VirtualHost>' \
    > /etc/apache2/sites-available/000-default.conf

# Prepare storage, bootstrap cache and public storage symlink
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             storage/app/public \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan storage:link

# Copy entrypoint script for dynamic port binding and runtime setup
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
