# Etapa 1 — Build do frontend
FROM node:22-alpine AS build
WORKDIR /app

COPY package*.json ./
RUN npm install --legacy-peer-deps
COPY . .
RUN npm run build

# Etapa 2 — PHP-FPM
FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

RUN apk add --no-cache git zip unzip curl libpng-dev oniguruma-dev libxml2-dev \
    nodejs npm supervisor bash postgresql-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia os arquivos do Laravel
COPY . .

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Copia os assets gerados no build do frontend
COPY --from=build /app/public/build ./public/build

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache public/build \
    && chmod -R 775 storage bootstrap/cache public/build

EXPOSE 9000
CMD ["/bin/sh", "/var/www/html/start.sh"]
