# ============================
# Etapa 1 - Build do Frontend
# ============================
FROM node:20-alpine AS build-frontend

WORKDIR /app

# Copia apenas arquivos necessários para o frontend
COPY package*.json ./
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./
COPY tsconfig.json ./
COPY resources/ ./resources/

RUN npm install --legacy-peer-deps
RUN npm run build

# ============================
# Etapa 2 - Backend PHP (Laravel)
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

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia código da aplicação
COPY . .

# Copia build do frontend
COPY --from=build-frontend /app/public/build ./public/build/

# Instala dependências PHP
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Script de inicialização
COPY docker-start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-start.sh

EXPOSE 8000

CMD ["/usr/local/bin/docker-start.sh"]