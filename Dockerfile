# ============================
# Etapa 1 — Composer (dependências PHP)
# ============================
FROM composer:2 AS vendor

WORKDIR /app

# Copia apenas os arquivos necessários para o Composer
COPY composer.json composer.lock ./

# Instala dependências de produção (sem dev)
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --no-scripts

# ============================
# Etapa 2 — Build do Frontend (Vite + Vue)
# ============================
FROM node:20-alpine AS frontend

WORKDIR /app

# Copia arquivos de dependência do Node
COPY package*.json vite.config.* ./
RUN npm ci --no-audit --prefer-offline

# Copia código-fonte
COPY resources ./resources
COPY public ./public
COPY artisan ./
COPY --from=vendor /app/vendor ./vendor

# Compila assets para produção
RUN npm run build

# ============================
# Etapa 3 — Imagem final (PHP-FPM + Nginx)
# ============================
FROM php:8.2-fpm

# Instala Nginx, gettext (para envsubst) e extensões PHP
RUN apt-get update && apt-get install -y \
    nginx \
    gettext \
    git \
    unzip \
    zip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    && rm -rf /var/lib/apt/lists/*

# Remove configuração padrão do Nginx
RUN rm -f /etc/nginx/sites-enabled/default

# Copia template do Nginx (com ${PORT})
COPY nginx.conf.template /etc/nginx/sites-available/default.template

# Diretório da aplicação
WORKDIR /var/www/html

# Copia código da aplicação
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public ./public
COPY . .

# Permissões corretas para Laravel
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Copia entrypoint e dá permissão
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Porta padrão (Render sobrescreve com $PORT)
EXPOSE 8080

# Entrypoint inicia tudo
ENTRYPOINT ["/entrypoint.sh"]