# ✅ 1️⃣ Rasm: PHP Apache bilan
FROM php:8.2-apache

# ✅ 2️⃣ SQLite va ext-pdo_sqlite ni o‘rnat
RUN apt-get update \
    && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# ✅ 3️⃣ Loyihani konteynerga nusxala
COPY . /var/www/html/

# ✅ 4️⃣ Ruxsatlarni to‘g‘ri qil — SQLite yozishga ruxsat
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# ✅ 5️⃣ Apache portini och
EXPOSE 80
