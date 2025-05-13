#!/bin/bash




set -e
cd "$(dirname "$0")/.." || exit 1
LARAVEL_VERSION=$(php artisan --version | grep -oE '[0-9]+')
LARAVEL_MAJOR_VERSION=$(echo "$LARAVEL_FULL_VERSION" | grep -oE '[0-9]+' | head -n1)

# Verifica se é Laravel 12
if [ "$LARAVEL_MAJOR_VERSION" -ne 9 ]; then
  echo "❌ Para atualizar para o Laravel 10, precisa estar na versão 9 (detectado: $LARAVEL_MAJOR_VERSION)."
  exit 1
fi
echo "🚀 Iniciando atualização para Laravel 10..."

# Backup
DATA=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="backup_pre_update_$DATA"
mkdir -p $BACKUP_DIR
cp composer.json composer.lock $BACKUP_DIR/
cp -r app config routes $BACKUP_DIR/


# Atualizando versão do Laravel no composer.json
echo "Remove o pacote fruitcake/laravel-cors"
composer remove fruitcake/laravel-cors
sed -i "s|\\\Fruitcake\\\Cors\\\HandleCors::class|\\\Illuminate\\\Http\\\Middleware\\\HandleCors::class|g" app/Http/Kernel.php

echo "Remoção completada do pacote fruitcake/laravel-cors com sucesso"

echo "📦 Atualizando composer.json..."
sed -i 's/"laravel\/framework": "[^"]*"/"laravel\/framework": "^10.0"/' composer.json
sed -i 's/"laravel\/sanctum": "[^"]*"/"laravel\/sanctum": "^3.2"/' composer.json
sed -i 's/"laravel\/passport": "[^"]*"/"laravel\/passport": "^11.0"/' composer.json
sed -i 's/"laravel\/ui": "[^"]*"/"laravel\/ui": "^4.0"/' composer.json
sed -i 's/"doctrine\/dbal": "[^"]*"/"doctrine\/dbal": "^3.0"/' composer.json
sed -i 's/"facade\/ignition": "[^"]*"/"spatie\/laravel-ignition": "^2.0"/' composer.json
sed -i 's/"ylsideas\/feature-flags": "[^"]*"/"ylsideas\/feature-flags": "^3.0"/' composer.json
sed -i 's/"nunomaduro\/collision": "[^"]*"/"nunomaduro\/collision": "^7.0"/' composer.json
sed -i 's/"phpunit\/phpunit": "[^"]*"/"phpunit\/phpunit": "^10.0"/' composer.json
sed -i 's/"php": "[^"]*"/"php": "^8.1"/' composer.json
sed -i 's/\(<coverage[^>]*\) processUncoveredFiles="[^"]*"/\1/' phpunit.xml

## Update de dependências
#echo "📦 atualizando composer ..."
#composer self-update --2


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
