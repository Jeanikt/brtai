#!/bin/sh

set -e

cd /var/www/html

echo "=== Starting Application ==="

# Cria .env se não existir
if [ ! -f .env ]; then
    echo "Creating .env from example..."
    cp .env.example .env
fi

# Configura APP_KEY
if [ -n "$APP_KEY" ]; then
    echo "Setting APP_KEY from environment..."
    sed -i "s/APP_KEY=.*/APP_KEY=$APP_KEY/" .env
elif ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating new APP_KEY..."
    php artisan key:generate --force
fi

# Cache de configurações
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Laravel server on port 8000..."
php artisan serve --host=0.0.0.0 --port=8000