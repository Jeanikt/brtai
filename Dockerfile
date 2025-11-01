# Etapa 1: imagem base PHP
FROM php:8.3-fpm AS base

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libssl-dev libicu-dev g++ \
    && docker-php-ext-install pdo pdo_mysql bcmath zip intl opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos da aplicação (apenas composer.* para cache eficiente)
COPY composer.json composer.lock ./

# Instalar dependências PHP (sem dev inicialmente)
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --no-progress \
    || composer update --no-dev --no-scripts --prefer-dist --no-interaction --no-progress

# Copiar restante do código-fonte
COPY . .

# Rodar scripts do Laravel
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Permissões para o storage e bootstrap
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ------------------------------------------------
# Etapa 2: Node.js para build de assets (Vite)
# ------------------------------------------------
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build

# ------------------------------------------------
# Etapa 3: Container final
# ------------------------------------------------
FROM base AS final

WORKDIR /var/www/html

# Copiar build do front-end
COPY --from=frontend /app/public/build ./public/build

EXPOSE 9000

CMD ["php-fpm"]
