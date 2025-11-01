# ============================
# Etapa 1 - Dependências PHP (Vendor)
# ============================
FROM composer:2.7 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
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
# Etapa 3 - Runtime (PHP-FPM + Nginx)
# ============================
FROM php:8.3-fpm-alpine

# Instala dependências do sistema
RUN apk add --no-cache \
    nginx \
    bash \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    supervisor \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip gd exif

# Configura Nginx
COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/conf.d/default.conf

# Copia código da aplicação
WORKDIR /var/www/html
COPY . .

# Copia vendor e build
COPY --from=vendor /app/vendor ./vendor
COPY --from=build-frontend /app/public/build ./public/build

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Script de inicialização
COPY docker-start.sh /usr/local/bin/docker-start.sh
RUN chmod +x /usr/local/bin/docker-start.sh

# Expoe porta do Render
EXPOSE 10000

# Inicia Supervisor (gerencia PHP-FPM + Nginx)
CMD ["/usr/local/bin/docker-start.sh"]