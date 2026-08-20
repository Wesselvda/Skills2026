#!/usr/bin/env bash
set -e
cd /app
[ -f .env ] || cp .env.prod .env

if [ -n "${APP_KEY:-}" ]; then
  sed -i "s#^APP_KEY=.*#APP_KEY=${APP_KEY}#" .env
elif ! grep -q '^APP_KEY=.\+' .env; then
  php artisan key:generate --force --no-interaction
fi

exec php artisan serve --host 0.0.0.0 --port 80
