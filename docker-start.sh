#!/bin/sh
set -e

cd /var/www/html

echo "=== Iniciando Laravel no Render ==="

if [ ! -f .env ]; then
    cp .env.example .env
    chmod 644 .env
fi

if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

sed -i "s|APP_URL=.*|APP_URL=https://brotai.com.br|g" .env
sed -i "s|VITE_APP_NAME=.*|VITE_APP_NAME=\"BRTai\"|g" .env

if [ ! -f public/build/manifest.json ]; then
    echo "⚠️  Assets do Vite não encontrados. Executando build..."
    npm ci --legacy-peer-deps
    npm run build
fi

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

chown -R www-data:www-data storage bootstrap/cache public/build
chmod -R 775 storage bootstrap/cache
chmod -R 755 public/build

echo "✅ Configuração concluída"
echo "Iniciando Supervisor..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
