FROM php:8.1-apache
RUN apt-get update && apt-get install -y libzip-dev zip unzip
RUN apt-get update && apt-get install -y sqlite3 libsqlite3-dev pkg-config
RUN docker-php-ext-install pdo pdo_sqlite
COPY --chown=www-data:www-data public/ /var/www/html/
COPY --chown=www-data:www-data src/ /var/www/src/
WORKDIR /var/www
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install || true
