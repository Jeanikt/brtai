# ============================
# Etapa 1 - Build do Frontend
# ============================
FROM node:20 AS build-frontend

WORKDIR /app

# Copiar os arquivos necessários para instalar dependências
COPY package*.json ./

# Atualizar npm para versão estável
RUN npm install -g npm@latest

# Instalar dependências do frontend (ignorando conflitos de peer)
RUN npm install --legacy-peer-deps

# Copiar todo o código do projeto
COPY . .

# Criar diretório fictício do Ziggy para evitar erro no build
RUN mkdir -p vendor/tightenco/ziggy && echo "export default {};" > vendor/tightenco/ziggy/index.js

# Gerar build de produção do Vite
RUN npm run build


# ============================
# Etapa 2 - Backend PHP (Laravel)
# ============================
FROM php:8.3-fpm

# Instalar dependências do sistema e extensões PHP
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Criar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do Laravel
COPY . .

# Copiar build gerado do frontend
COPY --from=build-frontend /app/public ./public

# Instalar dependências do Laravel
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Gerar cache de configuração
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Corrigir permissões
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
