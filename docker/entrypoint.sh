#!/bin/bash

set -e

echo "🚀 Iniciando BrotaAI..."

# Inicia Redis
redis-server --daemonize yes

# Verifica .env
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

# Otimiza Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrações
php artisan migrate --force

# Permissões
chmod -R 775 storage bootstrap/cache

# Inicia serviços
php-fpm8.2 -D
nginx -g 'daemon off;'