# PHPning rasmiy Apache imagini olamiz
FROM php:8.2-apache

# Tizim kutubxonalarini yangilash va kerakli dev paketlarini o‘rnatish
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev

# PHP cURL extension ni o‘rnatish
RUN docker-php-ext-install curl

# Apache uchun rewrite modulini yoqish (agar kerak bo‘lsa)
RUN a2enmod rewrite

# Loyihani containerga nusxalash
COPY . /var/www/html

# Apache document rootni sozlash (optional)
# RUN sed -i 's|/var/www/html|/var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Port
EXPOSE 80
