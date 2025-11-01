#!/bin/sh
set -e

cd /var/www/html

echo "=== Iniciando Laravel no Render ==="

# Cria .env se não existir
if [ ! -f .env ]; then
    echo "Criando .env a partir de .env.example..."
    cp .env.example .env
fi

# Configura APP_KEY
if [ -n "$APP_KEY" ]; then
    echo "APP_KEY fornecida via variável de ambiente"
    sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|g" .env
elif ! grep -q "APP_KEY=base64:" .env; then
    echo "Gerando nova APP_KEY..."
    php artisan key:generate --force
fi

# Cache de configuração
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Inicia Supervisor
echo "Iniciando Supervisor (Nginx + PHP-FPM)..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf