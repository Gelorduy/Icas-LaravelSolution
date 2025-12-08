# filepath: communications/Dockerfile
FROM php:8.2-cli
WORKDIR /var/www/comm
RUN apt-get update && apt-get install -y git unzip libzip-dev netcat-openbsd \
  && docker-php-ext-install pdo_mysql zip
# Copy composer and install dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --no-dev --prefer-dist 2>/dev/null || composer install --no-interaction --prefer-dist
# Run a simple PHP server that listens on port 6001 (placeholder for WebSocket/Reverb)
CMD ["php", "-S", "0.0.0.0:6001"]
