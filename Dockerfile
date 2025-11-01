# ============================
# Etapa 1 — Dependências PHP (Ziggy)
# ============================
FROM composer:2 AS ziggy
WORKDIR /app

# Copia composer.json e composer.lock
COPY composer.json composer.lock ./

# Garante compatibilidade entre composer.json e composer.lock
# Se o lock estiver desatualizado, faz update automático
RUN composer validate --no-check-all \
    && composer install --no-dev --no-scripts --prefer-dist --no-interaction \
    || (echo "composer.lock desatualizado — atualizando..." && composer update --no-dev --no-scripts --prefer-dist --no-interaction)


# ============================
# Etapa 2 — Build do Frontend (Vite)
# ============================
FROM node:22-alpine AS build
WORKDIR /app

# Copia apenas arquivos essenciais
COPY package*.json ./

# Instala dependências Node
RUN npm ci --legacy-peer-deps

# Copia todo o projeto
COPY . .

# Copia vendor da etapa anterior (para Ziggy)
COPY --from=ziggy /app/vendor ./vendor

# Gera build de produção (Vite)
RUN npm run build


# ============================
# Etapa 3 — Produção (PHP-FPM + Laravel)
# ============================
FROM php:8.3-fpm-alpine

# Define o diretório de trabalho
WORKDIR /var/www/html

# Instala dependências do sistema e extensões PHP necessárias
RUN apk add --no-cache \
    git zip unzip curl bash supervisor nodejs npm postgresql-dev \
    libpng-dev oniguruma-dev libxml2-dev icu-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd intl

# Copia o Composer da imagem oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia os arquivos da aplicação
COPY . .

# Copia build do frontend
COPY --from=build /app/public/build ./public/build

# Instala dependências PHP otimizadas
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Ajusta permissões
RUN chown -R www-data:www-data storage bootstrap/cache public/build \
    && chmod -R 775 storage bootstrap/cache public/build

# Copia e configura script de inicialização
COPY ./docker-start.sh /var/www/html/start.sh
RUN chmod +x /var/www/html/start.sh

# Porta do PHP-FPM
EXPOSE 9000

# Comando de inicialização
CMD ["/bin/sh", "/var/www/html/start.sh"]
