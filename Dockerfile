# ================================
# Etapa 1: Backend (Composer + PHP)
# ================================
FROM php:8.2-fpm AS backend

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip zip curl libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instala o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia os arquivos necessários e instala dependências PHP
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist

# Copia o restante do projeto Laravel
COPY . .

# ================================
# Etapa 2: Frontend (Vite)
# ================================
FROM node:20 AS frontend

WORKDIR /app

# Copia o projeto inteiro (já com vendor do backend)
COPY --from=backend /var/www/html /app

RUN npm ci
RUN npm run build

# ================================
# Etapa 3: Produção final (PHP + Nginx)
# ================================
FROM php:8.2-fpm AS production

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y nginx && apt-get clean

# Copia o Laravel completo e o build do frontend
COPY --from=backend /var/www/html ./
COPY --from=frontend /app/public/build ./public/build

# Permissões adequadas
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

# Script de inicialização
RUN echo '#!/bin/bash' > /start.sh && \
    echo 'php artisan config:cache' >> /start.sh && \
    echo 'php artisan route:cache' >> /start.sh && \
    echo 'php artisan view:cache' >> /start.sh && \
    echo 'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache' >> /start.sh && \
    echo 'chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache' >> /start.sh && \
    echo 'service nginx start && php-fpm' >> /start.sh && \
    chmod +x /start.sh

CMD ["/start.sh"]
