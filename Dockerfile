# ============================
# Etapa 1 — Build do Frontend
# ============================
FROM node:22-alpine AS build
WORKDIR /app

# Copia dependências
COPY package*.json composer.json composer.lock ./

# Instala PHP mínimo + Composer
RUN apk add --no-cache php php-cli php-mbstring php-dom php-tokenizer php-simplexml git curl \
    && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Instala vendor (para Ziggy)
RUN composer install --no-dev --no-scripts --prefer-dist

# Instala dependências Node
RUN npm ci --legacy-peer-deps

# Copia o código completo
COPY . .

# Garante pasta de build
RUN mkdir -p public/build

# Gera o build
RUN npm run build


# ============================
# Etapa 2 — Ambiente PHP-FPM
# ============================
FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

RUN apk add --no-cache git zip unzip curl bash supervisor nodejs npm postgresql-dev \
    libpng-dev oniguruma-dev libxml2-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
COPY --from=build /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

RUN chown -R www-data:www-data storage bootstrap/cache public/build \
    && chmod -R 775 storage bootstrap/cache public/build

COPY ./docker-start.sh /var/www/html/start.sh
RUN chmod +x /var/www/html/start.sh

EXPOSE 9000
CMD ["/bin/sh", "/var/www/html/start.sh"]
