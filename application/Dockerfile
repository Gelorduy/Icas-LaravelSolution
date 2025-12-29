# filepath: application/Dockerfile
FROM php:8.2-fpm
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y git unzip libzip-dev libpng-dev zlib1g-dev nodejs npm python3 python3-pip python3-venv \
    && docker-php-ext-install pdo_mysql zip

RUN pip3 install --break-system-packages --no-cache-dir \
    ezdxf==1.4.3 svgwrite==1.4.3 pillow==11.0.0

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# composer binary (optional; will be mounted/used in dev)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ensure permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
