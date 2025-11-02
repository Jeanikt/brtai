# Etapa 1 — Backend (PHP + Composer)
FROM php:8.2-fpm AS backend

WORKDIR /var/www/html

# Instala dependências do sistema e PHP
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia arquivos do Laravel
COPY . .

# Instala dependências PHP (sem dev)
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Gera cache de config e rotas
RUN php artisan config:cache && php artisan route:cache

# Etapa 2 — Frontend (Node + Vite)
FROM node:20-bookworm AS frontend

WORKDIR /app

COPY --from=backend /var/www/html /app

RUN npm install && npm run build

# Etapa 3 — Produção (Nginx + PHP-FPM)
FROM nginx:alpine AS production

# Instala PHP-FPM e extensões no Alpine
RUN apk add --no-cache php82 php82-fpm php82-opcache php82-pdo php82-pdo_mysql php82-mbstring php82-tokenizer php82-xml php82-gd php82-fileinfo

WORKDIR /var/www/html

# Copia aplicação compilada
COPY --from=backend /var/www/html ./
COPY --from=frontend /app/public/build ./public/build

# Copia configuração do Nginx
COPY ./nginx.conf /etc/nginx/nginx.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm82 -D && nginx -g 'daemon off;'"]
