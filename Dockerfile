# ============================
# Etapa 1 - Build do Frontend
# ============================
FROM node:20 AS build-frontend

# Definir diretório de trabalho
WORKDIR /app

# Copiar apenas os arquivos necessários para instalar dependências
COPY package*.json ./

# Atualizar o npm para evitar bugs de resolução de dependências
RUN npm install -g npm@latest

# Instalar dependências do frontend (ignorando conflitos)
RUN npm install --legacy-peer-deps

# Copiar todo o código para o container
COPY . .

# Gerar build de produção do Vue (Vite)
RUN npm run build


# ============================
# Etapa 2 - Backend PHP (Laravel)
# ============================
FROM php:8.3-fpm

# Instalar dependências do sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Criar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do Laravel (exceto node_modules, vendor, etc.)
COPY . .

# Copiar build do frontend da etapa anterior
COPY --from=build-frontend /app/public ./public

# Instalar dependências do Laravel
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Gerar cache de configuração
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Permitir escrita em storage e bootstrap
RUN chmod -R 775 storage bootstrap/cache

# Expor a porta padrão do Laravel
EXPOSE 8000

# Comando padrão de execução
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
