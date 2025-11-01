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

# Atualiza variáveis se existirem no arquivo
if grep -q "APP_URL=" .env; then
    sed -i "s|APP_URL=.*|APP_URL=https://brotai.com.br|g" .env
else
    echo "APP_URL=https://brotai.com.br" >> .env
fi

if grep -q "VITE_APP_NAME=" .env; then
    sed -i "s|VITE_APP_NAME=.*|VITE_APP_NAME=\"BRTai\"|g" .env
else
    echo "VITE_APP_NAME=\"BRTai\"" >> .env
fi

# Verifica se o manifest existe (não deve precisar buildar no container)
if [ ! -f public/build/manifest.json ]; then
    echo "❌ ERRO: manifest.json não encontrado! O build do frontend deve ser feito na etapa de build do Docker."
    ls -la public/ || echo "📂 Pasta public não existe."
    exit 1
fi

# Otimizações Laravel
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Permissões
chown -R www-data:www-data storage bootstrap/cache public/build
chmod -R 775 storage bootstrap/cache public/build

echo "✅ Laravel pronto e otimizado para produção!"
exec php-fpm
