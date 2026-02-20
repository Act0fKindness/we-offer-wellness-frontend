#!/usr/bin/env bash
set -euo pipefail

# Simple Laravel + Vite deploy script
# Usage:
#   ./scripts/deploy.sh [branch] [--no-migrate] [--no-build]
# Defaults:
#   branch = current checked-out branch

BRANCH="${1:-}"
NO_MIGRATE="false"
NO_BUILD="false"

for arg in "$@"; do
  case "$arg" in
    --no-migrate) NO_MIGRATE="true" ;;
    --no-build)   NO_BUILD="true" ;;
  esac
done

if [[ -z "$BRANCH" || "$BRANCH" == --* ]]; then
  BRANCH=$(git rev-parse --abbrev-ref HEAD)
fi

echo "==> Deploying branch: $BRANCH"

if ! command -v php >/dev/null 2>&1; then echo "php not found in PATH" >&2; exit 1; fi
if ! command -v composer >/dev/null 2>&1; then echo "composer not found in PATH" >&2; exit 1; fi

# Pull latest code
echo "==> Fetching and pulling latest code"
git fetch --all --prune
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

# PHP dependencies
echo "==> Installing PHP dependencies (composer)"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Optimize Laravel caches
echo "==> Optimizing Laravel caches"
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan optimize

# Run DB migrations (unless suppressed)
if [[ "$NO_MIGRATE" != "true" ]]; then
  echo "==> Running database migrations"
  php artisan migrate --force
else
  echo "==> Skipping migrations (--no-migrate)"
fi

# Frontend build (Vite) — requires Node/npm
if [[ "$NO_BUILD" != "true" ]]; then
  if command -v npm >/dev/null 2>&1; then
    echo "==> Installing Node deps and building assets (Vite)"
    # Prefer clean, reproducible install if lockfile exists
    if [[ -f package-lock.json ]]; then npm ci --no-audit --no-fund; else npm install --no-audit --no-fund; fi
    npm run build
  else
    echo "!! npm not found; skipping Vite build. Ensure public/build exists or install Node/npm."
  fi
else
  echo "==> Skipping asset build (--no-build)"
fi

# Fallback CSS copy (in case build/manifest absent but we want base styles)
if [[ ! -f public/build/manifest.json ]]; then
  echo "==> Ensuring base stylesheet fallback is present"
  mkdir -p public/css
  if [[ -f resources/css/we-offer-wellness-base-styles.css ]]; then
    cp -f resources/css/we-offer-wellness-base-styles.css public/css/we-offer-wellness-base-styles.css
  fi
fi

echo "==> Done. Branch $BRANCH deployed."

