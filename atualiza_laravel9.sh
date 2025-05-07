#!/bin/bash




set -e
cd "$(dirname "$0")/.." || exit 1
LARAVEL_VERSION=$(php artisan --version | grep -oE '[0-9]+')
LARAVEL_MAJOR_VERSION=$(echo "$LARAVEL_FULL_VERSION" | grep -oE '[0-9]+' | head -n1)

# Verifica se é Laravel 12
if [ "$LARAVEL_MAJOR_VERSION" -ne 8 ]; then
  echo "❌ Para atualizar para o Laravel 9, precisa estar na versão 8 (detectado: $LARAVEL_MAJOR_VERSION)."
  exit 1
fi
echo "🚀 Iniciando atualização para Laravel 9..."

# Backup
DATA=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="backup_pre_update_$DATA"
mkdir -p $BACKUP_DIR
cp composer.json composer.lock $BACKUP_DIR/
cp -r app config routes $BACKUP_DIR/

# Atualizando versão do Laravel no composer.json
echo "📦 Atualizando composer.json..."
sed -i 's/"laravel\/framework": "[^"]*"/"laravel\/framework": "^9.0"/' composer.json
sed -i 's/"nunomaduro\/collision": "[^"]*"/"nunomaduro\/collision": "^6.1"/' composer.json
sed -i 's/"facade\/ignition": "[^"]*"/"spatie\/laravel-ignition": "^1.0"/' composer.json
sed -i 's/"pusher\/pusher-php-server": "[^"]*"/"pusher\/pusher-php-server": "^5.0"/' composer.json
sed -i 's/"php": "[^"]*"/"php": "^8.0.2"/' composer.json


# Update de dependências
echo "📦 Rodando composer update..."
composer update --with-all-dependencies

echo "📦 Atualizando o arquivo trust proxy"

sed -i 's|protected \$headers = Request::HEADER_X_FORWARDED_ALL;|protected \$headers =\
    Request::HEADER_X_FORWARDED_FOR \|\
    Request::HEADER_X_FORWARDED_HOST \|\
    Request::HEADER_X_FORWARDED_PORT \|\
    Request::HEADER_X_FORWARDED_PROTO \|\
    Request::HEADER_X_FORWARDED_AWS_ELB;|' app/Http/Middleware/TrustProxies.php


echo "📦 Removendo o pacote  fideloper/proxy"
composer remove fideloper/proxy

# Limpar e recachear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "✅ Laravel atualizado (verifique e teste tudo com atenção)"
echo "✅ Rode o teste para verificar se estar tudo ok!!"
