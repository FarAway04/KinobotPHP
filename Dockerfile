# Rasm asosida PHP + curl
FROM php:8.1-cli

# cURL va SSL extensionlarini qo‘sh
RUN docker-php-ext-install curl

# Kodingni konteyner ichiga ko‘chir
COPY . /app

# Ishchi katalog
WORKDIR /app

# 80 port och (PHP server uchun zarur emas, lekin ba’zi platformalar tekshiradi)
EXPOSE 80

# Ishga tushirish komandasi (nginx yo‘q, faqat PHP ishlaydi)
CMD ["php", "-S", "0.0.0.0:80"]
