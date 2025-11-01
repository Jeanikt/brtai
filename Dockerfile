# ============================
# Etapa 1 — Build do Frontend
# ============================
FROM composer:2 AS ziggy
WORKDIR /app

# Copia arquivos necessários do Laravel para instalar o Ziggy
COPY composer.json composer.lock ./

# Instala dependências PHP mínimas (apenas para Ziggy)
RUN composer install --no-dev --no-scripts --prefer-dist


# ============================
# Etapa 2 — Build do Frontend (Node + Vite)
# ============================
FROM node:22-alpine AS build
WORKDIR /app

# Copia apenas os arquivos necessários para instalar dependências
COPY package*.json ./

# Instala dependências Node
RUN npm ci --legacy-peer-deps

# Copia o restante do código
COPY . .

# Copia a pasta vendor do estágio anterior (para Ziggy)
COPY --from=ziggy /app/vendor ./vendor

# Faz o build do frontend (Vite)
RUN npm run build



# ============================
# Etapa 3 — PHP-FPM (Produção)
# ============================
FROM php:8.3-fpm-alpine

# Define diretório de trabalho
WORKDIR /var/www/html

# Instala dependências do sistema e extensões PHP necessárias
RUN apk add --no-cache \
    git zip unzip curl bash supervisor nodejs npm postgresql-dev \
    libpng-dev oniguruma-dev libxml2-dev icu-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd intl

# Copia o Composer do container oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia o código Laravel
COPY . .

# Copia o build do frontend (gerado na etapa anterior)
COPY --from=build /app/public/build ./public/build

# Instala dependências PHP otimizadas
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Ajusta permissões
RUN chown -R www-data:www-data storage bootstrap/cache public/build \
    && chmod -R 775 storage bootstrap/cache public/build

# Copia e dá permissão ao script de inicialização
COPY ./docker-start.sh /var/www/html/start.sh
RUN chmod +x /var/www/html/start.sh

# Expõe a porta padrão do PHP-FPM
EXPOSE 9000

# Comando de inicialização
CMD ["/bin/sh", "/var/www/html/start.sh"]
