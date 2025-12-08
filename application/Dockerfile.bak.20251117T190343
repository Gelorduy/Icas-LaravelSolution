# filepath: application/Dockerfile
FROM php:8.2-fpm
WORKDIR /var/www/html
RUN apt-get update && apt-get install -y git unzip libzip-dev \
  && docker-php-ext-install pdo_mysql zip
# Copy composer and install (local dev will mount volume)
