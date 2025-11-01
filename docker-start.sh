#!/bin/sh

set -e

cd /var/www/html

# Debug: verifica se .env existe e mostra APP_KEY
echo "=== DEBUG ==="
ls -la .env || echo ".env not found"
echo "APP_KEY from env: $APP_KEY"
echo "============="

# Força criação do .env se não existir
if [ ! -f .env ]; then
    echo "Creating .env from example..."
    cp .env.example .env
fi

# Garante que a APP_KEY está no .env (se fornecida via env var)
if [ -n "$APP_KEY" ]; then
    echo "Setting APP_KEY in .env from environment variable..."
    sed -i "s/APP_KEY=.*/APP_KEY=$APP_KEY/" .env
fi

# Gera key se ainda estiver vazia
if grep -q "APP_KEY=" .env && ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Limpa caches antigos
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache de configurações para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=8000