# ============================
# Etapa 1 — Instala dependências PHP e Composer
# ============================
FROM composer:2 AS vendor

WORKDIR /app

# Copia os arquivos necessários para o Composer
COPY composer.json composer.lock ./

# Instala dependências de produção sem rodar scripts do Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# ============================
# Etapa 2 — Build do Frontend (Vite + Ziggy)
# ============================
FROM node:20 AS frontend

WORKDIR /app

# Copia arquivos de dependência do Node
COPY package*.json vite.config.* ./
RUN npm ci --no-audit --prefer-offline

# Copia recursos e dependências para Ziggy
COPY resources ./resources
COPY public ./public
COPY --from=vendor /app/vendor ./vendor
COPY artisan ./

# Compila os assets de produção
RUN npm run build

# ============================
# Etapa 3 — Imagem final PHP (produção)
# ============================
FROM php:8.2-fpm AS production

WORKDIR /var/www/html

# Instala extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    git unzip zip curl libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Copia a aplicação e dependências
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public ./public
COPY . .

# Permissões corretas
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copia entrypoint para inicialização dinâmica
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]

CMD ["php-fpm"]
