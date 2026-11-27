#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

echo "==> [1/7] Ativando modo de manutenção..."
php artisan down --render="errors::503" || true

echo "==> [2/7] Atualizando código-fonte (git pull)..."
git pull origin main

echo "==> [3/7] Instalando dependências PHP de produção..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "==> [4/7] Compilando assets frontend (Vite)..."
npm ci
npm run build

echo "==> [5/7] Executando migrations..."
php artisan migrate --force

echo "==> [6/7] Otimizando caches do Laravel e Filament..."
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:optimize

echo "==> [7/7] Reiniciando workers e encerrando modo de manutenção..."
php artisan queue:restart
php artisan up

echo "✅ Deploy concluído com sucesso!"
