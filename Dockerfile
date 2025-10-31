# ============================
# Etapa 1 - Build do Frontend
# ============================
FROM node:20 AS build-frontend

# Define o diretório de trabalho
WORKDIR /app

# Copia os arquivos de dependências do Node
COPY package*.json ./

# Atualiza o npm e instala dependências (com compatibilidade)
RUN npm install -g npm@latest
RUN npm install --legacy-peer-deps

# Copia o restante do código para dentro do container
COPY . .

# Cria stub do Ziggy para evitar erro de importação no build (mock de plugin Vue)
RUN mkdir -p vendor/tightenco/ziggy && \
    echo "export default {}; export const ZiggyVue = { install() {} }; " > vendor/tightenco/ziggy/index.js

# Gera build de produção do Vue (Vite)
RUN npm run build


# ============================
# Etapa 2 - Backend PHP (Laravel)
# ============================
FROM php:8.3-fpm

# Instala dependências necessárias do Laravel
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia o código do projeto Laravel
COPY . .

# Copia os arquivos buildados do frontend para a pasta pública do Laravel
COPY --from=build-frontend /app/public ./public

# Instala dependências PHP sem dev
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Cacheia configurações e rotas
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Ajusta permissões de pastas importantes
RUN chmod -R 775 storage bootstrap/cache

# Expõe a porta usada pelo Laravel
EXPOSE 8000

# Comando padrão do container
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
