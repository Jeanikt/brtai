# Etapa 1 - Build do front com Node
FROM node:22-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Etapa 2 - Laravel com PHP-FPM
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache git zip unzip curl libpng-dev oniguruma-dev libxml2-dev nodejs npm supervisor \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
RUN composer install --no-dev --optimize-autoloader
COPY --from=build /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
