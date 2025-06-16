# PHP Apache bazasi
FROM php:8.2-apache

# PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Apache uchun rewrite yoqish (agar kerak bo‘lsa)
RUN a2enmod rewrite

# KODLARNI KOPIYALASH
COPY . /var/www/html/

# (ixtiyoriy) Apache user permission
RUN chown -R www-data:www-data /var/www/html

# 80-port ochish
EXPOSE 80
