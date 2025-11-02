# ================================
# Etapa 1: Construção do frontend
# ================================
FROM node:20 AS frontend

WORKDIR /app

COPY package*.json vite.config.* ./
COPY resources ./resources
COPY public ./public

# Copia vendor e composer.json para permitir acesso ao Ziggy
COPY vendor ./vendor
COPY composer.json composer.lock ./

RUN npm ci
RUN npm run build

# ================================
# Etapa 2: Backend Laravel + PHP
# ================================
FROM php:8.2-fpm AS production

RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev zip curl nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www/html

COPY . .

# Copia build do frontend
COPY --from=frontend /app/public/build ./public/build

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

RUN echo '#!/bin/bash' > /start.sh && \
    echo 'php artisan config:cache' >> /start.sh && \
    echo 'php artisan route:cache' >> /start.sh && \
    echo 'php artisan view:cache' >> /start.sh && \
    echo 'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache' >> /start.sh && \
    echo 'chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache' >> /start.sh && \
    echo 'service nginx start && php-fpm' >> /start.sh && \
    chmod +x /start.sh

CMD ["/start.sh"]
