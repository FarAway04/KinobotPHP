# PHP image
FROM php:8.1-apache

# Curl va sqlite o‘rnatish
RUN docker-php-ext-install pdo pdo_sqlite

# Apache index.php o‘rniga Bot.php ni asosiy qilamiz
COPY . /var/www/html/

# Port
EXPOSE 80
