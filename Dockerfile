FROM php:8.2-apache

# PHP extension lar
RUN docker-php-ext-install pdo pdo_sqlite

# Apache conf va DocumentRoot
WORKDIR /var/www/html

COPY . /var/www/html

EXPOSE 80
