#!/bin/bash

set -e

echo "🚀 Iniciando BrotaAI..."

# Inicia Redis
echo "🔴 Iniciando Redis..."
redis-server --daemonize yes

# Configura .env se necessário
if [ ! -f .env ]; then
    echo "📄 Criando .env..."
    cp .env.example .env
    php artisan key:generate --force
fi

# Otimizações Laravel
echo "⚡ Otimizando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrações
echo "🗃️  Verificando migrações..."
php artisan migrate --force

# Permissões
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Inicia serviços
echo "🌐 Iniciando servidores..."
php-fpm8.2 -D
echo "✅ BrotaAI rodando na porta 8080"
nginx -g 'daemon off;'