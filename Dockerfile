FROM composer:2.7.9 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM node:20-alpine AS build-frontend

WORKDIR /app

COPY package*.json vite.config.js tailwind.config.js postcss.config.js tsconfig.json ./
COPY resources ./resources

RUN npm install --legacy-peer-deps
RUN npm run build

FROM php:8.3.13-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    libpng \
    libjpeg-turbo \
    libzip \
    libpq

RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev

RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip gd exif
RUN apk del --purge .build-deps
RUN rm -rf /var/cache/apk/* /tmp/*

RUN apk add --no-cache nodejs npm

COPY etc/nginx/nginx.conf /etc/nginx/nginx.conf
COPY etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html
COPY . .

COPY --from=vendor /app/vendor ./vendor
COPY --from=build-frontend /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache public/build

COPY docker-start.sh /usr/local/bin/docker-start.sh
RUN chmod +x /usr/local/bin/docker-start.sh

EXPOSE 10000

CMD ["/usr/local/bin/docker-start.sh"]
