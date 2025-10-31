# ============================
# Etapa 1 - Build do Frontend
# ============================
FROM node:20 AS build-frontend

WORKDIR /app

COPY package*.json ./

RUN npm install -g npm@latest
RUN npm install --legacy-peer-deps

COPY . .

# Criar stub do Ziggy para evitar erro de importação no build
RUN mkdir -p vendor/tightenco/ziggy && \
    echo "export default {}; export const ZiggyVue = {}; " > vendor/tightenco/ziggy/index.js

RUN npm run build


# ============================
# Etapa 2 - Backend PHP (Laravel)
# ============================
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY . .

COPY --from=build-frontend /app/public ./public

RUN composer install --no-dev --no-interaction --optimize-autoloader

RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
