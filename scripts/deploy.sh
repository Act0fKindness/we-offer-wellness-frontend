#!/usr/bin/env bash
set -euo pipefail

# Simple Laravel + Vite deploy script
# Usage:
#   ./scripts/deploy.sh [branch] [--no-migrate] [--no-build] [--no-verify]
# Defaults:
#   branch = current checked-out branch

BRANCH="${1:-}"
NO_MIGRATE="false"
NO_BUILD="false"
NO_VERIFY="false"

for arg in "$@"; do
  case "$arg" in
    --no-migrate) NO_MIGRATE="true" ;;
    --no-build)   NO_BUILD="true" ;;
    --no-verify)  NO_VERIFY="true" ;;
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

# Sanity checks for Blade partials that affect styling (avoid shipping a broken head)
if [[ "$NO_VERIFY" != "true" ]]; then
  echo "==> Verifying Blade layout/partials integrity"
  HEAD_PARTIAL="resources/views/partials/head.blade.php"
  LAYOUT="resources/views/layouts/app.blade.php"
  HOME="resources/views/home/index.blade.php"
  if [[ ! -f "$HEAD_PARTIAL" ]]; then
    echo "ERROR: Missing $HEAD_PARTIAL" >&2; exit 1
  fi
  if ! rg -n "@vite\('resources/js/app.js'\)" "$HEAD_PARTIAL" >/dev/null 2>&1; then
    echo "ERROR: $HEAD_PARTIAL missing @vite('resources/js/app.js'). Assets won't load." >&2; exit 1
  fi
  if ! rg -n "@include\('partials\.styles'\)" "$HEAD_PARTIAL" >/dev/null 2>&1; then
    echo "ERROR: $HEAD_PARTIAL missing @include('partials.styles'). Base CSS won't apply." >&2; exit 1
  fi
  if ! rg -n "@inertiaHead" "$HEAD_PARTIAL" >/dev/null 2>&1; then
    echo "WARN: $HEAD_PARTIAL missing @inertiaHead (Inertia page head won't hydrate)." >&2
  fi
  if ! rg -n "@include\('partials\.head'\)" "$LAYOUT" >/dev/null 2>&1; then
    echo "ERROR: $LAYOUT missing @include('partials.head') in <head>." >&2; exit 1
  fi
  if ! rg -n "<main[^
>]*>\s*@yield\('content'\)\s*</main>" -U "$LAYOUT" >/dev/null 2>&1; then
    echo "ERROR: $LAYOUT <main> does not yield('content')." >&2; exit 1
  fi
  if ! rg -n "@extends\('layouts\.app'\)" "$HOME" >/dev/null 2>&1; then
    echo "WARN: $HOME does not extend the app layout." >&2
  fi
fi

# PHP dependencies
echo "==> Installing PHP dependencies (composer)"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Optimize & warm Laravel caches
echo "==> Optimizing Laravel caches"
php artisan optimize:clear || true
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
    if [[ ! -f public/build/manifest.json ]]; then
      echo "ERROR: Vite build did not produce public/build/manifest.json" >&2
      exit 1
    else
      echo "==> Vite manifest present: public/build/manifest.json"
    fi
  else
    echo "!! npm not found; skipping Vite build. Ensure public/build exists or install Node/npm."
  fi
else
  echo "==> Skipping asset build (--no-build)"
fi

# Fallback CSS copy (rare) — keep as a last resort if manifest is absent and build is skipped
if [[ ! -f public/build/manifest.json && "$NO_BUILD" == "true" ]]; then
  echo "==> Ensuring base stylesheet fallback is present (no-build mode)"
  mkdir -p public/css
  if [[ -f resources/css/we-offer-wellness-base-styles.css ]]; then
    cp -f resources/css/we-offer-wellness-base-styles.css public/css/we-offer-wellness-base-styles.css
  fi
fi

echo "==> Done. Branch $BRANCH deployed."
