FROM php:8.2-apache

# Apache config ixtiyoriy
WORKDIR /var/www/html

# SQLITE3 kerakli paketlarni o‘rnat
RUN apt-get update && \
    apt-get install -y sqlite3 libsqlite3-dev && \
    docker-php-ext-install pdo pdo_sqlite

# Hamma fayllarni konteynerga nusxala
COPY . /var/www/html

EXPOSE 80
