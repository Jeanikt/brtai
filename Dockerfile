# -----------------------------
# Etapa 1: Build do frontend (Vite + Vue 3 / Inertia)
# -----------------------------
FROM node:20 AS build-frontend
WORKDIR /app

# Instalar dependências do frontend
COPY package*.json ./
RUN npm ci

# Copiar todo o código e gerar o build de produção
COPY . .
RUN npm run build


# -----------------------------
# Etapa 2: Backend (Laravel 12 + PHP 8.3)
# -----------------------------
FROM php:8.3-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Criar diretório da aplicação
WORKDIR /var/www/html

# Copiar código da aplicação
COPY . .

# Copiar o build do frontend
COPY --from=build-frontend /app/public ./public

# Instalar dependências do Laravel em modo produção
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Otimizações do framework
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && php artisan optimize

# Ajustar permissões
RUN chown -R www-data:www-data storage bootstrap/cache

# Variáveis padrão (Render substitui via painel)
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_URL=https://your-app.onrender.com
ENV REDIS_CLIENT=predis
ENV REDIS_PORT=6379

# Expor a porta usada pelo Render
EXPOSE 10000

# Comando de inicialização
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
