#!/bin/sh
set -e

cd /var/www/html

echo "=== 🚀 Iniciando Laravel no Render ==="

# Cria .env se não existir
if [ ! -f .env ]; then
    cp .env.example .env
    chmod 644 .env
fi

# Gera chave se necessário
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Atualiza variáveis
sed -i "s|APP_URL=.*|APP_URL=https://brotai.com.br|g" .env
sed -i "s|VITE_APP_NAME=.*|VITE_APP_NAME=\"BRTai\"|g" .env

# Garante que o manifest existe
if [ ! -f public/build/manifest.json ]; then
    echo "⚠️ Assets não encontrados. Executando build..."
    npm ci --legacy-peer-deps
    npm run build
fi

# Verificação final
if [ ! -f public/build/manifest.json ]; then
    echo "❌ ERRO: manifest.json não encontrado!"
    ls -la public/build/ || echo "📂 Pasta public/build não existe."
    exit 1
fi

# Otimizações do Laravel
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Permissões finais
chown -R www-data:www-data storage bootstrap/cache public/build
chmod -R 775 storage bootstrap/cache
chmod -R 755 public/build

echo "✅ Setup completo e Laravel pronto!"
exec php-fpm
