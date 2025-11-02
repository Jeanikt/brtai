# ============================
# Etapa 1 — Instala dependências PHP e Composer
# ============================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# ============================
# Etapa 2 — Build do Frontend (Vite + Ziggy)
# ============================
FROM node:20 AS frontend

WORKDIR /app

COPY package*.json vite.config.* ./
RUN npm ci --no-audit --prefer-offline

COPY resources ./resources
COPY public ./public
COPY --from=vendor /app/vendor ./vendor
COPY artisan ./

RUN npm run build

# ============================
# Etapa 3 — Imagem final com PHP-FPM + Nginx
# ============================
FROM php:8.2-fpm

# Instala Nginx e extensões PHP
RUN apt-get update && apt-get install -y \
    nginx \
    git unzip zip curl libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Configuração do Nginx
COPY ./nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Copia aplicação
WORKDIR /var/www/html
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public ./public
COPY . .

# Permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Entrypoint
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Porta do Render (variável de ambiente)
EXPOSE $PORT

ENTRYPOINT ["/entrypoint.sh"]
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]