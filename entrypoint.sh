#!/bin/sh
set -e

# Gera APP_KEY se não existir
if [ ! -f .env ]; then
  echo "⚠️  Nenhum arquivo .env encontrado! Crie ou defina variáveis de ambiente."
else
  if ! grep -q "APP_KEY=base64" .env; then
    echo "🔑 Gerando APP_KEY..."
    php artisan key:generate --force
  fi
fi

echo "⚙️ Limpando e gerando caches do Laravel..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan event:clear || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan event:cache || true

echo "🚀 Inicializando PHP-FPM..."
exec "$@"
