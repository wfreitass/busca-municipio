FROM composer:2 AS composer

WORKDIR /var/www/html
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=composer /var/www/html/vendor ./vendor
COPY backend ./
COPY docs/api/openapi.yaml /var/www/docs/api/openapi.yaml
COPY censo.sqlite ./database/censo.sqlite

RUN cp .env.example .env \
    && php artisan key:generate --force \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod 0444 database/censo.sqlite

EXPOSE 80

CMD ["apache2-foreground"]
