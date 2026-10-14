
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev libicu-dev \
    nodejs npm \
    && docker-php-ext-install pdo_pgsql pgsql bcmath intl zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

RUN npm ci && npm run build

RUN printf '%s\n' \
    '<VirtualHost *:80>' \
    'DocumentRoot /var/www/html/public' \
    '<Directory /var/www/html/public>' \
    'AllowOverride All' \
    'Require all granted' \
    '</Directory>' \
    '</VirtualHost>' \
    > /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
