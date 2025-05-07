#!/bin/bash

echo "Escolha uma opção:"
echo "1) Atualizar para o laravel 12"
echo "2) Atualizar para o laravel 11"
echo "3) Atualizar para o laravel 9"
echo "4) Sair"

read -p "Digite o número da opção desejada: " OPCAO

case $OPCAO in
  1)
    ./laravel-tools/atualiza_laravel12.sh
    ;;
  2)
    ./laravel-tools/atualiza_laravel11.sh
    ;;
  3)
      ./laravel-tools/atualiza_laravel9.sh
      ;;
  4)
    echo "👋 Saindo..."
    exit 0
    ;;
  *)
    echo "❌ Opção inválida."
    exit 1
    ;;
esac
