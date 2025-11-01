# ===============================================================
# 🧱 Etapa base: PHP com extensões essenciais
# ===============================================================
FROM php:8.2-fpm AS base

# Instala dependências de sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libssl-dev libicu-dev g++ \
    && docker-php-ext-install pdo pdo_mysql bcmath zip intl opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Instala Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html


# ===============================================================
# 🧰 Etapa frontend: build do Vite + Ziggy
# ===============================================================
FROM node:20 AS frontend

WORKDIR /app

# Copia os arquivos necessários para instalar dependências
COPY package*.json vite.config.* ./
COPY tsconfig*.json ./
COPY resources ./resources
COPY composer.json composer.lock ./
COPY artisan ./

# Instala Composer (para gerar rotas do Ziggy)
RUN apt-get update && apt-get install -y curl git unzip \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Instala dependências PHP (para Ziggy) e JS
RUN composer install --no-dev --no-interaction --prefer-dist
RUN npm install

# Build do Vite
RUN npm run build


# ===============================================================
# 🧩 Etapa final: imagem leve pronta para produção
# ===============================================================
FROM base AS production

WORKDIR /var/www/html

# Copia o backend Laravel
COPY . .

# Copia o build frontend gerado
COPY --from=frontend /app/public/build ./public/build

# Copia dependências PHP (vendor)
COPY --from=frontend /app/vendor ./vendor

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Define variáveis de ambiente padrão
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_KEY=base64:B0Rn6M7dRH6PdG3O4btbhK0uHQb2tyzdPWC8xj4E/qY=

# Porta de exposição
EXPOSE 9000

# Comando de inicialização do PHP-FPM
CMD ["php-fpm"]
