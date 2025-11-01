# ============================
# Etapa 1 - Dependências PHP (Vendor)
# ============================
FROM composer:2.7 AS vendor

WORKDIR /app

# Copia tudo para garantir que artisan exista
COPY . .

# Instala dependências PHP
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts


# ============================
# Etapa 2 - Build do Frontend
# ============================
FROM node:20-alpine AS build-frontend

WORKDIR /app

COPY package*.json vite.config.js tailwind.config.js postcss.config.js tsconfig.json ./ 
COPY resources ./resources
RUN npm ci --legacy-peer-deps
RUN npm run build


# ============================
# Etapa 3 - Backend PHP (Laravel)
# ============================
FROM php:8.3-fpm-alpine

RUN apk add --no-cache bash curl libpng-dev libjpeg-turbo-dev libzip-dev zip unzip oniguruma-dev postgresql-dev supervisor

RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip gd

WORKDIR /var/www/html

# Copia código da aplicação
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=build-frontend /app/public/build ./public/build/

RUN chown -R www-data:www-data /var/www/html && chmod -R 755 storage bootstrap/cache

COPY docker-start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-start.sh

EXPOSE 8000

CMD ["/usr/local/bin/docker-start.sh"]
