# ================================
# Etapa 1: Construção do frontend
# ================================
FROM node:20 AS frontend

WORKDIR /app
COPY package*.json vite.config.* ./
COPY resources ./resources
COPY public ./public

RUN npm ci
RUN npm run build

# ================================
# Etapa 2: Backend Laravel + PHP
# ================================
FROM php:8.2-fpm AS production

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev zip curl nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia arquivos do Laravel
COPY . .

# Copia build do frontend
COPY --from=frontend /app/public/build ./public/build

# Instala dependências do Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Permissões para o Laravel
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copia configuração do Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expõe porta padrão HTTP
EXPOSE 80

# Cria script de inicialização
RUN echo '#!/bin/bash' > /start.sh && \
    echo 'php artisan config:cache' >> /start.sh && \
    echo 'php artisan route:cache' >> /start.sh && \
    echo 'php artisan view:cache' >> /start.sh && \
    echo 'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache' >> /start.sh && \
    echo 'chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache' >> /start.sh && \
    echo 'service nginx start && php-fpm' >> /start.sh && \
    chmod +x /start.sh

CMD ["/start.sh"]
