#!/bin/sh
set -e

# Define porta padrão caso Render não defina (útil localmente)
PORT=${PORT:-8080}

# Substitui ${PORT} no template e gera o config final
echo "Configurando Nginx para porta $PORT..."
envsubst '${PORT}' < /etc/nginx/sites-available/default.template > /etc/nginx/sites-available/default

# Garante que o site está habilitado
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/ || true

# Gera APP_KEY se necessário
if [ ! -f .env ]; then
  echo "Nenhum .env encontrado. Usando variáveis de ambiente do Render."
else
  if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "Gerando APP_KEY..."
    php artisan key:generate --force
  fi
fi

echo "Otimizações do Laravel..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan event:clear || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan event:cache || true

echo "Iniciando PHP-FPM e Nginx na porta $PORT..."
php-fpm -D  # Roda em background
nginx -g 'daemon off;'  # Roda em foreground