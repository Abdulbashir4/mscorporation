#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
test -f .env || { echo 'Create .env from .env.production.example first.'; exit 1; }
test -f public/build/manifest.json || { echo 'Upload public/build from your local production build first.'; exit 1; }
"$COMPOSER_BIN" install --no-dev --prefer-dist --optimize-autoloader --no-interaction
"$PHP_BIN" artisan config:clear
"$PHP_BIN" -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); if (! $app->environment("production") || config("app.debug") || ! config("app.key") || ! config("app.access_username") || ! config("app.access_password")) { fwrite(STDERR, "Set production environment, debug=false, APP_KEY and access credentials in .env first.\n"); exit(1); }'
"$PHP_BIN" artisan migrate --force --no-interaction
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan queue:restart
printf 'Deployment complete. Confirm HTTPS and the domain document root points to this project/public.\n'
