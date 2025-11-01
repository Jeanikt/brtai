# ============================
# Etapa 1 - Instala dependências PHP (para ter Ziggy)
# ============================
FROM composer:2.7 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

# ============================
# Etapa 2 - Build do Frontend
# ============================
FROM node:20-alpine AS build-frontend

WORKDIR /app

# Copia vendor primeiro (para ter Ziggy disponível)
COPY --from=vendor /app/vendor ./vendor

# Copia arquivos do frontend
COPY package*.json ./
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./
COPY tsconfig.json ./
COPY resources/ ./resources/

RUN npm ci --legacy-peer-deps
RUN npm run build

# ============================
# Etapa 3 - Backend PHP (Laravel)
# ============================
FROM php:8.3-fpm-alpine

# Instala dependências do sistema
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    postgresql-dev \
    supervisor

# Extensões PHP necessárias
RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip gd

WORKDIR /var/www/html

# Copia código da aplicação
COPY . .

# Copia vendor das dependências PHP
COPY --from=vendor /app/vendor ./vendor

# Copia build do frontend
COPY --from=build-frontend /app/public/build ./public/build/

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Script de inicialização
COPY docker-start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-start.sh

EXPOSE 8000

CMD ["/usr/local/bin/docker-start.sh"]