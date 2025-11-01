#!/bin/sh
set -e

cd /var/www/html

echo "=== Iniciando Laravel no Render ==="

# Cria .env
if [ ! -f .env ]; then
    cp .env.example .env
    chmod 644 .env
fi

# FORÇA HTTPS
sed -i "s|APP_URL=.*|APP_URL=https://brotai.com.br|g" .env
sed -i "s|VITE_APP_NAME=.*|VITE_APP_NAME=\"BRTai\"|g" .env

# Gera APP_KEY
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Cache
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Permissões
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Iniciando Supervisor..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf