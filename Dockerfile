# ============================
# Etapa 1 — Build do Frontend
# ============================
FROM node:22-alpine AS build
WORKDIR /app

# Copia apenas arquivos necessários para instalar dependências Node
COPY package*.json ./

# Instala dependências do Node
RUN npm ci --legacy-peer-deps

# Copia todo o código-fonte
COPY . .

# Garante que o diretório de saída existe
RUN mkdir -p public/build

# Faz o build do frontend (Vite + TypeScript)
RUN npm run build


# ============================
# Etapa 2 — PHP-FPM (produção)
# ============================
FROM php:8.3-fpm-alpine

# Define diretório de trabalho
WORKDIR /var/www/html

# Instala dependências do sistema e extensões PHP necessárias
RUN apk add --no-cache \
    git zip unzip curl bash supervisor nodejs npm postgresql-dev \
    libpng-dev oniguruma-dev libxml2-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Copia o Composer do container oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia o código Laravel (sem o build ainda)
COPY . .

# Copia o build do frontend (Vite) gerado na etapa anterior
COPY --from=build /app/public/build ./public/build

# Instala dependências PHP otimizadas
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Ajusta permissões para execução
RUN chown -R www-data:www-data storage bootstrap/cache public/build \
    && chmod -R 775 storage bootstrap/cache public/build

# Copia e dá permissão ao script de inicialização
COPY ./docker-start.sh /var/www/html/start.sh
RUN chmod +x /var/www/html/start.sh

# Expõe a porta padrão do PHP-FPM
EXPOSE 9000

# Comando de inicialização
CMD ["/bin/sh", "/var/www/html/start.sh"]
