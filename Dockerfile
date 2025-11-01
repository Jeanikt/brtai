# ============================
# Etapa 1 — Build do Frontend
# ============================
FROM node:22-alpine AS build
WORKDIR /app

# Instala PHP + Composer (para gerar rotas Ziggy)
RUN apk add --no-cache \
    php php-cli php-mbstring php-dom php-tokenizer php-simplexml php-fileinfo curl git \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia arquivos necessários
COPY package*.json composer.json composer.lock ./

# Instala dependências PHP (para Ziggy)
RUN composer install --no-dev --no-scripts --prefer-dist

# Instala dependências Node
RUN npm ci --legacy-peer-deps

# Copia o restante do código do projeto
COPY . .

# Garante a pasta de build
RUN mkdir -p public/build

# Faz o build do frontend (Vite + TypeScript + Ziggy)
RUN npm run build



# ============================
# Etapa 2 — PHP-FPM (produção)
# ============================
FROM php:8.3-fpm-alpine

# Define diretório de trabalho
WORKDIR /var/www/html

# Instala dependências do sistema e extensões PHP necessárias para Laravel
RUN apk add --no-cache \
    git zip unzip curl bash supervisor nodejs npm postgresql-dev \
    libpng-dev oniguruma-dev libxml2-dev icu-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd intl

# Copia o Composer do container oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia todos os arquivos Laravel
COPY . .

# Copia o build do frontend feito na primeira etapa
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

# Inicia o container
CMD ["/bin/sh", "/var/www/html/start.sh"]
