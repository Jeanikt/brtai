# ============================
# Etapa 1 — Build do Frontend
# ============================
FROM node:22-alpine AS build
WORKDIR /app

# Copia arquivos principais
COPY package*.json ./
COPY composer.json composer.lock ./   # Necessário para instalar Ziggy antes do build

# Instala PHP básico para rodar o Composer (só o necessário para Ziggy)
RUN apk add --no-cache php php-cli php-mbstring php-dom php-tokenizer php-simplexml curl git

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Instala dependências PHP mínimas (apenas para gerar vendor/tightenco/ziggy)
RUN composer install --no-dev --no-scripts --prefer-dist

# Instala dependências Node
RUN npm install --legacy-peer-deps

# Copia o restante dos arquivos
COPY . .

# Faz o build do frontend
RUN npm run build


# ============================
# Etapa 2 — PHP-FPM (produção)
# ============================
FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

# Instala dependências do sistema e extensões PHP
RUN apk add --no-cache git zip unzip curl libpng-dev oniguruma-dev libxml2-dev \
    nodejs npm supervisor bash postgresql-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Copia o Composer do container oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia o código do Laravel
COPY . .

# Instala dependências PHP (agora completas)
RUN composer install --no-dev --optimize-autoloader

# Copia os assets gerados do build do frontend
COPY --from=build /app/public/build ./public/build

# Ajusta permissões
RUN chown -R www-data:www-data storage bootstrap/cache public/build \
    && chmod -R 775 storage bootstrap/cache public/build

# Porta padrão do PHP-FPM
EXPOSE 9000

# Inicia o container
CMD ["/bin/sh", "/var/www/html/start.sh"]
