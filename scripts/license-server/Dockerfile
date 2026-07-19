FROM php:8.3-fpm-alpine

LABEL org.opencontainers.image.title="HotelHub License Server"
LABEL org.opencontainers.image.description="Vendor license server for HotelHub HMS"
LABEL org.opencontainers.image.version="1.0"

RUN apk add --no-cache \
    nginx supervisor \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev icu-dev oniguruma-dev libxml2-dev \
    bash git curl tzdata openssl openssl-dev \
    mariadb-client

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd intl zip soap xml opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Redis & Imagick
RUN apk add --no-cache --virtual .build-deps autoconf g++ make pkgconf imagemagick-dev \
 && pecl install redis imagick \
 && docker-php-ext-enable redis imagick \
 && apk del .build-deps

WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install PHP dependencies (production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Copy service configurations
COPY scripts/license-server/nginx.conf /etc/nginx/nginx.conf
COPY scripts/license-server/supervisord.conf /etc/supervisord.conf
COPY scripts/license-server/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Ensure storage directories exist
RUN mkdir -p /var/www/storage/app /var/www/storage/framework/views \
         /var/www/storage/framework/cache /var/www/storage/framework/sessions \
         /var/www/storage/logs \
 && chown -R www-data:www-data /var/www/storage

EXPOSE 80 443

ENV TZ=Asia/Jakarta

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
