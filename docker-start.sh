#!/bin/sh
set -e

cd /var/www/html

echo "=== Iniciando Laravel no Render ==="

# Cria .env se não existir
if [ ! -f .env ]; then
    echo "Criando .env a partir de .env.example..."
    cp .env.example .env
fi

# GERA APP_KEY (OBRIGATÓRIO!)
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Gerando APP_KEY..."
    php artisan key:generate --force
fi

# Cache de configuração
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Inicia Supervisor
echo "Iniciando Supervisor (Nginx + PHP-FPM)..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf