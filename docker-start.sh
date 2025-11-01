#!/bin/sh
set -e

cd /var/www/html

echo "=== Starting Laravel on Render ==="

# Cria .env se não existir
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Configura APP_KEY
if [ -n "$APP_KEY" ]; then
    echo "Using provided APP_KEY"
    sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|g" .env
elif ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Limpa e cacheia configs
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Inicia Supervisor (Nginx + PHP-FPM)
echo "Starting Supervisor..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf