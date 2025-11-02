# Etapa 1: Composer
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Etapa 2: Frontend (Vite + Vue)
FROM node:20 AS frontend
WORKDIR /app
COPY package*.json vite.config.* ./
RUN npm ci --no-audit
COPY resources ./resources
COPY public ./public
COPY --from=vendor /app/vendor ./vendor
COPY artisan ./
RUN npm run build

# Etapa 3: Produção com PHP-FPM + Nginx
FROM php:8.2-fpm

# Instala Nginx + extensões
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Remove config padrão
RUN rm -f /etc/nginx/sites-enabled/default

# Copia template do Nginx
COPY nginx.conf.template /etc/nginx/sites-available/default.template

# Copia app
WORKDIR /var/www/html
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public ./public
COPY . .

# Permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Porta será definida pelo Render
EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]