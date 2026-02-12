#!/bin/sh
set -e

# Criar .env a partir do exemplo se não existir
if [ ! -f .env ]; then
  cp .env.example .env
fi

# Instalar dependências (ignora erros de platform quando rodando no container)
composer install --no-interaction --ignore-platform-reqs 2>/dev/null || composer install --no-interaction

# Gerar chave da aplicação se ainda não existir
if grep -q 'APP_KEY=$' .env 2>/dev/null || [ -z "$APP_KEY" ]; then
  php artisan key:generate --no-interaction --force
fi

# Rodar migrations pendentes
php artisan migrate --force --no-interaction

# Rodar seeds
php artisan db:seed --force --no-interaction

exec php artisan serve --host=0.0.0.0 --port=8000
