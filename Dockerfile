# ============================
# Etapa 1 — Instala dependências PHP e Composer
# ============================
FROM composer:2 AS vendor

WORKDIR /app

# Copia os arquivos de dependência PHP
COPY composer.json composer.lock ./

# Instala apenas dependências de produção
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# ============================
# Etapa 2 — Build do Frontend (Vite)
# ============================
FROM node:20 AS frontend

WORKDIR /app

# Copia os arquivos do frontend
COPY package*.json vite.config.* ./
RUN npm ci --no-audit --prefer-offline

# Copia recursos do frontend
COPY resources ./resources
COPY public ./public

# Copia dependências PHP para o Ziggy
COPY --from=vendor /app/vendor ./vendor
COPY artisan ./

# Compila os assets para produção
RUN npm run build

# ============================
# Etapa 3 — Imagem final PHP (produção)
# ============================
FROM php:8.2-fpm AS production

WORKDIR /var/www/html

# Instala extensões necessárias do Laravel
RUN apt-get update && apt-get install -y \
    git unzip zip curl libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Copia os arquivos do backend e dependências
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public ./public
COPY . .

# Ajusta permissões (importante para o Laravel rodar corretamente)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Otimiza o Laravel para produção
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan event:cache

EXPOSE 9000

CMD ["php-fpm"]
