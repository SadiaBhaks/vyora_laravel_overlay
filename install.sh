#!/usr/bin/env bash
# Applies the VYORA overlay to a brand-new Laravel app.
# Usage:  ./install.sh /path/to/new-app-dir
# Requires: PHP 8.2+, Composer, Node 20+, and a database (see .env).
set -euo pipefail
SRC="$(cd "$(dirname "$0")" && pwd)"
APP="${1:?Usage: ./install.sh /path/to/app}"

FRESH=0
if [ ! -f "$APP/artisan" ]; then
  FRESH=1
  echo "Creating a fresh Laravel app in $APP ..."
  composer create-project laravel/laravel "$APP"
fi

echo "Copying VYORA files over the app (overwrites routes/web.php, routes/console.php, bootstrap/app.php, package.json, vite.config.js, app.css/app.js) ..."
rsync -a --exclude install.sh --exclude README.md "$SRC"/ "$APP"/

cd "$APP"
composer require livewire/livewire stripe/stripe-php
# Optional Google sign-in:  composer require laravel/socialite

if [ "$FRESH" = 1 ]; then cp .env.example .env; else echo "Existing .env kept — compare it with .env.example (VYORA_* and STRIPE_* keys)."; fi
php artisan key:generate
php artisan storage:link

echo
echo "Edit .env with your database settings, then run:"
echo "  php artisan migrate --seed"
echo "  php artisan vyora:create-admin you@example.com --name=\"Your Name\""
echo "  npm install && npm run build      # or: composer run dev"
echo "  php artisan test"
