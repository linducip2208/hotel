FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    nginx supervisor \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev icu-dev oniguruma-dev libxml2-dev \
    bash git curl tzdata postgresql-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql pdo_pgsql mysqli mbstring exif pcntl bcmath gd intl zip soap xml opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Redis & Imagick
RUN apk add --no-cache --virtual .build-deps autoconf g++ make pkgconf imagemagick-dev \
 && pecl install redis imagick \
 && docker-php-ext-enable redis imagick \
 && apk del .build-deps

WORKDIR /var/www

COPY . /var/www
RUN composer install --no-dev --optimize-autoloader --no-interaction \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Copy nginx + supervisor config
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80
ENV TZ=Asia/Jakarta

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
