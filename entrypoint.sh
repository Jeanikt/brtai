#!/bin/sh
set -e

# Porta do Render
PORT=${PORT:-8080}
echo "Configurando Nginx na porta $PORT..."

# Gera config do Nginx com a porta correta
envsubst '${PORT}' < /etc/nginx/sites-available/default.template > /etc/nginx/sites-available/default
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/ || true

# Cria .env se não existir
if [ ! -f .env ]; then
    echo "Criando .env a partir de .env.example..."
    cp .env.example .env
    php artisan key:generate --force
else
    if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
        echo "Gerando APP_KEY..."
        php artisan key:generate --force
    fi
fi

# Otimizações do Laravel
echo "Otimizações do Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Inicia serviços
echo "Iniciando PHP-FPM (background) e Nginx (foreground) na porta $PORT..."
php-fpm -D
exec nginx -g 'daemon off;'