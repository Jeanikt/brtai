# ==========================
# 🐘 Etapa 1: Backend (Laravel + Composer)
# ==========================
FROM php:8.2-fpm AS backend

WORKDIR /var/www/html

# Instala dependências de sistema
RUN apt-get update && apt-get install -y \
    git curl unzip zip libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Copia Composer da imagem oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia os arquivos do Laravel
COPY . .

# Instala dependências PHP (inclui Ziggy)
RUN composer install --no-dev --no-interaction --prefer-dist

# ==========================
# 🧱 Etapa 2: Frontend (Vite)
# ==========================
FROM node:20-bookworm AS frontend

WORKDIR /app

# Copia o projeto completo (já vem com /vendor do estágio anterior)
COPY --from=backend /var/www/html /app

# Instala dependências do Node
RUN npm install

# Gera build de produção do Vite
RUN npm run build

# ==========================
# 🧩 Etapa 3: Produção final
# ==========================
FROM php:8.2-fpm AS production

WORKDIR /var/www/html

# Copia tudo do backend (Laravel completo)
COPY --from=backend /var/www/html ./

# Copia o build do frontend
COPY --from=frontend /app/public/build ./public/build

# Permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
