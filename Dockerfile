# ============================
# Etapa 1 - Dependências PHP (Vendor)
# ============================
FROM composer:2.7 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# ============================
# Etapa 2 - Build do Frontend (Vite)
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

# Instala pacotes do sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip gd exif

# Copia configurações
COPY etc/nginx/nginx.conf /etc/nginx/nginx.conf
COPY etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Diretório da aplicação
WORKDIR /var/www/html

# Copia código fonte
COPY . .

# Copia vendor e assets compilados
COPY --from=vendor /app/vendor ./vendor
COPY --from=build-frontend /app/public/build ./public/build

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Script de inicialização
COPY docker-start.sh /usr/local/bin/docker-start.sh
RUN chmod +x /usr/local/bin/docker-start.sh

# Porta do Render
EXPOSE 10000

# Inicia Supervisor (Nginx + PHP-FPM)
CMD ["/usr/local/bin/docker-start.sh"]