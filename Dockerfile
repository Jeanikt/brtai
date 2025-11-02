# Use uma imagem base com PHP 8.2
FROM ubuntu:22.04

# Evita prompts interativos durante a instalação
ENV DEBIAN_FRONTEND=noninteractive

# Adiciona repositório do PHP 8.2
RUN apt-get update && apt-get install -y \
    software-properties-common \
    && add-apt-repository ppa:ondrej/php -y \
    && apt-get update

# Instala dependências com PHP 8.2
RUN apt-get install -y \
    curl \
    nginx \
    php8.2 \
    php8.2-fpm \
    php8.2-pgsql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-bcmath \
    php8.2-redis \
    php8.2-tokenizer \
    php8.2-common \
    nodejs \
    npm \
    redis-server \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . .

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Instala dependências Node.js e build
RUN npm ci && npm run build

# Configura Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Configura PHP-FPM
RUN mkdir -p /var/run/php

# Configura Redis
RUN mkdir -p /var/run/redis && chown redis:redis /var/run/redis

# Expõe a porta
EXPOSE 8080

# Script de inicialização
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]