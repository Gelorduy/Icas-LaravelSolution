# filepath: communications/Dockerfile
FROM php:8.2-cli
WORKDIR /var/www/comm
RUN apt-get update && apt-get install -y git unzip libzip-dev \
  && docker-php-ext-install pdo_mysql zip
# Use for running websocket server / artisan queue or soketi binary
