#!/bin/sh
set -e

cd /var/www/html

echo "=== Iniciando Laravel no Render ==="

# 1. Cria .env se não existir
if [ ! -f .env ]; then
    echo "Criando .env a partir de .env.example..."
    cp .env.example .env
    chmod 644 .env
fi

# 2. GERA APP_KEY (ANTES de qualquer cache!)
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Gerando APP_KEY..."
    php artisan key:generate --force
fi

# 3. Limpa cache antigo
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Cache de configuração (AGORA com APP_KEY)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 5. Permissões finais
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 6. Inicia Supervisor
echo "Iniciando Supervisor (Nginx + PHP-FPM)..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf