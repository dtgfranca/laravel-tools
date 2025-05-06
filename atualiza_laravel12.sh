#!/bin/bash




set -e
cd "$(dirname "$0")/.." || exit 1
LARAVEL_VERSION=$(php artisan --version | grep -oE '[0-9]+')
LARAVEL_MAJOR_VERSION=$(echo "$LARAVEL_FULL_VERSION" | grep -oE '[0-9]+' | head -n1)

# Verifica se é Laravel 12
if [ "$LARAVEL_MAJOR_VERSION" -ne 11 ]; then
  echo "❌ Para atualizar para o Laravel 12, precisa estar na versão 11 (detectado: $LARAVEL_MAJOR_VERSION)."
  exit 1
fi
echo "🚀 Iniciando atualização para Laravel 12..."

# Backup
DATA=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="backup_pre_update_$DATA"
mkdir -p $BACKUP_DIR
cp composer.json composer.lock $BACKUP_DIR/
cp -r app config routes $BACKUP_DIR/

# Atualizando versão do Laravel no composer.json
echo "📦 Atualizando composer.json..."
sed -i 's/"laravel\/framework": "[^"]*"/"laravel\/framework": "^12.0"/' composer.json
sed -i 's/"phpunit\/phpunit": "[^"]*"/"phpunit\/phpunit": "^11.0"/' composer.json
sed -i 's/"tallstackui\/tallstackui": "[^"]*"/"tallstackui\/tallstackui": "^2.5.2"/' composer.json
sed -i 's/"pestphp\/pest": "[^"]*"/"pestphp\/pest": "^12.0"/' composer.json


# Update de dependências
echo "📦 Rodando composer update..."
composer update --with-all-dependencies

# Limpar e recachear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "✅ Laravel atualizado (verifique e teste tudo com atenção)"
echo "✅ Rode o teste para verificar se estar tudo ok!!"
