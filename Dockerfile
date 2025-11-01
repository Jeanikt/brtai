# ------------------------------------------------
# Etapa base: PHP com extensões necessárias
# ------------------------------------------------
FROM php:8.3-fpm AS base

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libssl-dev libicu-dev g++ \
    && docker-php-ext-install pdo pdo_mysql bcmath zip intl opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ------------------------------------------------
# Etapa frontend: build do Vite
# ------------------------------------------------
FROM node:20 AS frontend

WORKDIR /app

# Copia apenas o que é necessário para o build do Vite
COPY package*.json vite.config.* ./
COPY resources ./resources
COPY tsconfig*.json ./

# Copia vendor (necessário para Ziggy)
COPY vendor ./vendor
COPY artisan ./

# Instala dependências e faz o build
RUN npm install
RUN npm run build

# ------------------------------------------------
# Etapa final: aplicação pronta
# ------------------------------------------------
FROM base AS production

WORKDIR /var/www/html

# Copia arquivos da aplicação
COPY . .

# Copia o build do frontend
COPY --from=frontend /app/public/build ./public/build

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Define permissões
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
