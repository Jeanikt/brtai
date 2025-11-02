# Use uma imagem base com PHP 8.2 e Node.js 20
FROM ubuntu:22.04

# Evita prompts interativos durante a instalação
ENV DEBIAN_FRONTEND=noninteractive

# Adiciona repositórios do PHP 8.2 e Node.js 20
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl \
    gnupg \
    && add-apt-repository ppa:ondrej/php -y \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update

# Instala dependências
RUN apt-get install -y \
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
    redis-server \
    postgresql-client \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Diretório de trabalho
WORKDIR /var/www/html

# 🔥 COPIA TODOS OS ARQUIVOS PRIMEIRO (incluindo artisan)
COPY . .

# 🔥 DESABILITA SCRIPTS DO COMPOSER PARA EVITAR ERRO DO ARTISAN
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 🔥 AGORA EXECUTA OS SCRIPTS MANUALMENTE (após todos os arquivos estarem copiados)
RUN composer run-script post-autoload-dump

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