# PHP Apache bilan boshlaymiz
FROM php:8.2-apache

# zarur tizim kutubxonalarini o‘rnatamiz:
RUN apt-get update && \
    apt-get install -y libsqlite3-dev && \
    docker-php-ext-install pdo pdo_sqlite

# Loyihani containerga nusxalash
COPY . /var/www/html/
