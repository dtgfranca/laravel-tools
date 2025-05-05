#!/bin/bash




set -e

cd "$(dirname "$0")/.." || exit 1

LARAVEL_VERSION=$(php artisan --version)
LARAVEL_MAJOR_VERSION=$(echo "$LARAVEL_VERSION" | grep -oE '[0-9]+' | head -n1)

if ! [[ "$LARAVEL_MAJOR_VERSION" =~ ^[0-9]+$ ]]; then
  echo "❌ Não foi possível detectar a versão do Laravel."
  exit 1
fi

if [ "$LARAVEL_MAJOR_VERSION" -ne 10 ]; then
  echo "❌ Para atualizar para o Laravel 11, precisa estar na versão 10 (detectado: $LARAVEL_MAJOR_VERSION)."
  exit 1
fi

echo "🚀 Iniciando atualização para Laravel 11..."

# Backup
DATA=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="backup_pre_update_$DATA"
mkdir -p $BACKUP_DIR
cp composer.json composer.lock $BACKUP_DIR/
cp -r app config routes $BACKUP_DIR/

# Atualizando versão do Laravel no composer.json
echo "📦 Atualizando composer.json..."
sed -i 's/"laravel\/framework": "[^"]*"/"laravel\/framework": "^11.0"/' composer.json
sed -i 's/"nunomaduro\/collision": "[^"]*"/"nunomaduro\/collision": "^8.1"/' composer.json
sed -i 's/"laravel\/sanctum": "[^"]*"/"laravel\/sanctum": "^4.0"/' composer.json
sed -i 's/"laravel\/livewire": "[^"]*"/"laravel\/livewire": "^3.4"/' composer.json
sed -i 's/"laravel\/breeze": "[^"]*"/"laravel\/breeze": "^3.4"/' composer.json
sed -i 's/"laravel\/cashier": "[^"]*"/"laravel\/cashier": "^15.0"/' composer.json
sed -i 's/"laravel\/dusk": "[^"]*"/"laravel\/dusk": "^8.0"/' composer.json
sed -i 's/"laravel\/jetstream": "[^"]*"/"laravel\/jetstream": "^5.0"/' composer.json
sed -i 's/"laravel\/octane": "[^"]*"/"laravel\/octane": "^2.3"/' composer.json
sed -i 's/"laravel\/passport": "[^"]*"/"laravel\/passport": "^12.0"/' composer.json
sed -i 's/"laravel\/sanctum": "[^"]*"/"laravel\/sanctum": "^4.0"/' composer.json
sed -i 's/"laravel\/scout": "[^"]*"/"laravel\/scout": "^10.0"/' composer.json
sed -i 's/"laravel\/spark-stripe": "[^"]*"/"laravel\/spark-stripe": "^5.0"/' composer.json
sed -i 's/"laravel\/telescope": "[^"]*"/"laravel\/telescope": "^5.0"/' composer.json
sed -i 's/"livewire\/livewire": "[^"]*"/"livewire\/livewire": "^3.4"/' composer.json
sed -i 's/"inertiajs\/inertia-laravel": "[^"]*"/"laravel\/dusk": "^1.0"/' composer.json
sed -i 's/"php": "[^"]*"/"php": "^8.2"/' composer.json

# Update de dependências
echo "📦 Rodando composer update..."
composer update --with-all-dependencies

#publicando as migrations do sanctum,
PACOTE="laravel/sanctum"
# Verifica se o pacote está instalado
if composer show "$PACOTE" > /dev/null 2>&1; then
  echo "📦 Pacote $PACOTE encontrado. Publicando as migrations..."
  php artisan vendor:publish --tag=sanctum-migrations
else
  echo "❌ Pacote $PACOTE não está instalado. Nada a fazer."
fi

# publicando as migrations do Telescope
PACOTE_TELESCOPE="laravel/telescope"

if composer show "PACOTE_TELESCOPE" > /dev/null 2>&1; then
  echo "📦 Pacote PACOTE_TELESCOPE encontrado. Publicando as migrations..."
  php artisan vendor:publish --tag=telescope-migrations
else
  echo "❌ Pacote PACOTE_TELESCOPE não está instalado. Nada a fazer."
fi

# publicando as migrations do Passport
PACOTE_PASSPORT="laravel/passport"

if composer show "$PACOTE_PASSPORT" > /dev/null 2>&1; then
  echo "📦 Pacote $PACOTE_PASSPORT encontrado. Publicando as migrations..."
  php artisan vendor:publish --tag=passport-migrations
else
  echo "❌ Pacote $PACOTE_PASSPORT não está instalado. Nada a fazer."
fi

# Limpar e recachear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "✅ Laravel atualizado (verifique e teste tudo com atenção)"

echo "✅ Rode o teste para verificar se estar tudo ok!!"
