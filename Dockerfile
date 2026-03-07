FROM php:8.2-apache

# تثبيت PDO PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# نسخ الملفات
COPY . /var/www/html/