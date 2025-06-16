FROM php:8.2-apache

# Zarur extensionlarni o‘rnatish
RUN docker-php-ext-install pdo pdo_sqlite

# Apache DocumentRoot
WORKDIR /var/www/html

# Hammasini konteynerga nusxalash
COPY . /var/www/html

# Apache port ochish
EXPOSE 80
