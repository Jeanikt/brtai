# Use uma imagem base com PHP 8.2
FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Instala dependências básicas
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl \
    gnupg \
    && add-apt-repository ppa:ondrej/php -y \
    && apt-get update

# Instala PHP, Nginx e Redis
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
    nodejs \
    npm \
    redis-server \
    && rm -rf /var/lib/apt/lists/*

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# 🔥 COPIA TODOS OS ARQUIVOS (incluindo os assets já built)
COPY . .

# 🔥 INSTALA APENAS DEPENDÊNCIAS PHP (já temos os assets)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Configura Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Configura PHP-FPM e Redis
RUN mkdir -p /var/run/php /var/run/redis

EXPOSE 8080

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]