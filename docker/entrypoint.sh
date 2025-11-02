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

# 🔥 CORREÇÃO CRÍTICA: Verifica se o CSS está no manifest
echo "Verificando build do Vite..."
if [ -f public/build/manifest.json ]; then
    if grep -q "resources/css/app.css" public/build/manifest.json; then
        echo "✅ CSS encontrado no manifest."
    else
        echo "🚨 CSS NÃO encontrado no manifest. Rebuild necessário..."
        # Força rebuild com configuração correta
        npm run build
        
        # Verifica novamente
        if grep -q "resources/css/app.css" public/build/manifest.json; then
            echo "✅ CSS incluído após rebuild."
        else
            echo "❌ CSS ainda não encontrado. Verificando vite.config.js..."
            # Verifica a configuração do Vite
            if ! grep -q "resources/css/app.css" vite.config.js; then
                echo "⚠️  Corrigindo vite.config.js..."
                # Corrige temporariamente o vite.config.js
                sed -i 's/input: "resources\/js\/app.ts"/input: ["resources\/css\/app.css", "resources\/js\/app.ts"]/' vite.config.js
                npm run build
            fi
        fi
    fi
else
    echo "🚨 Manifest não encontrado. Executando build..."
    npm run build
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
chmod -R 775 storage bootstrap/cache public/build
chown -R www-data:www-data storage bootstrap/cache public/build

echo "Iniciando servidores..."
# Inicia PHP-FPM em background
php-fpm8.2 -D

# Inicia Nginx em foreground
echo "BrotaAI rodando na porta 8080"
nginx -g 'daemon off;'