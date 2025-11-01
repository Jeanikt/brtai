# ============================
# Etapa 1 - Dependências PHP (Vendor)
# ============================
FROM composer:2.7 AS vendor

WORKDIR /app

# Copia apenas os arquivos necessários para composer
COPY composer.json composer.lock ./

# Instala dependências PHP (sem dev, otimizado)
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ============================
# Etapa 2 - Build do Frontend (Vite + ziggy-js)
# ============================
FROM node:20-alpine AS build-frontend

WORKDIR /app

# Copia configs do frontend
COPY package*.json vite.config.js tailwind.config.js postcss.config.js tsconfig.json ./
COPY resources ./resources

# Instala dependências do Node (inclui ziggy-js)
RUN npm ci --legacy-peer-deps

# Executa o build do Vite
RUN npm run build

# ============================
# Etapa 3 - Runtime (PHP-FPM + Nginx)
# ============================
FROM php:8.3-fpm-alpine

# Instala pacotes do sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip gd exif

# Copia configurações do Nginx e Supervisor
COPY etc/nginx/nginx.conf /etc/nginx/nginx.conf
COPY etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Diretório da aplicação
WORKDIR /var/www/html

# Copia todo o código fonte
COPY . .

# Copia vendor (do stage 1) e assets compilados (do stage 2)
COPY --from=vendor /app/vendor ./vendor
COPY --from=build-frontend /app/public/build ./public/build

# Permissões corretas
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Copia script de inicialização
COPY docker-start.sh /usr/local/bin/docker-start.sh
RUN chmod +x /usr/local/bin/docker-start.sh

# Porta usada pelo Render
EXPOSE 10000

# Inicia o Supervisor (gerencia Nginx + PHP-FPM)
CMD ["/usr/local/bin/docker-start.sh"]