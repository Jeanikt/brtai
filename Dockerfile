# ============================
# Etapa 1 — Build do Frontend
# ============================
FROM node:22-alpine AS build

# Garante que o diretório exista
WORKDIR /app

# Copia apenas os manifests necessários
COPY package*.json ./
COPY composer.json composer.lock ./

# Instala PHP mínimo para rodar o Composer e gerar o Ziggy
RUN apk add --no-cache php php-cli php-mbstring php-dom php-tokenizer php-simplexml curl git

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Instala dependências PHP básicas (para Ziggy)
RUN composer install --no-dev --no-scripts --prefer-dist

# Instala dependências Node
RUN npm install --legacy-peer-deps

# Copia o restante do código
COPY . .

# Gera os assets de produção
RUN npm run build


# ============================
# Etapa 2 — PHP-FPM (produção)
# ============================
FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

# Instala pacotes e extensões PHP
RUN apk add --no-cache git zip unzip curl libpng-dev oniguruma-dev libxml2-dev \
    nodejs npm supervisor bash postgresql-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Copia o Composer do container oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia o código do Laravel
COPY . .

# Instala dependências PHP (modo produção)
RUN composer install --no-dev --optimize-autoloader

# Copia os assets gerados do build do frontend
COPY --from=build /app/public/build ./public/build

# Ajusta permissões
RUN chown -R www-data:www-data storage bootstrap/cache public/build \
    && chmod -R 775 storage bootstrap/cache public/build

# Expõe a porta do PHP-FPM
EXPOSE 9000

# Executa script de inicialização
CMD ["/bin/sh", "/var/www/html/start.sh"]
