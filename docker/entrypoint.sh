#!/bin/bash

set -e

echo "Iniciando configuração do BrotaAI..."

# Inicia Redis em background
echo "Iniciando Redis..."
redis-server --daemonize yes

# Verifica se .env existe, se não, cria a partir do exemplo
if [ ! -f .env ]; then
    echo "Criando .env a partir de .env.example..."
    cp .env.example .env
fi

# Garante que o APP_KEY está configurado
if ! grep -q "^APP_KEY=base64:S8dt04WJY9d1p23I7NEhTgwld2gZBaK8pPtW1jYj6LE=" .env; then
    echo "Configurando APP_KEY..."
    sed -i "s/^APP_KEY=.*/APP_KEY=base64:S8dt04WJY9d1p23I7NEhTgwld2gZBaK8pPtW1jYj6LE=/" .env
fi

# Otimizações do Laravel para produção
echo "Otimizando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Executa migrações do banco (se necessário)
echo "Verificando migrações do banco..."
php artisan migrate --force

# Ajusta permissões
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "Iniciando servidores..."
# Inicia PHP-FPM em background (agora php8.2-fpm)
php-fpm8.2 -D

# Inicia Nginx em foreground
echo "BrotaAI rodando na porta 8080"
nginx -g 'daemon off;'