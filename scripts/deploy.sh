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
STRICT_VERIFY="${STRICT_VERIFY:-false}"

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

# Ensure Laravel writable directories have correct permissions/ownership
echo "==> Ensuring permissions on storage and bootstrap/cache"
WEB_USER="${WEB_USER:-www-data}"
WEB_GROUP="${WEB_GROUP:-www-data}"
mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache || true
# Try chown if allowed; ignore errors on environments without sudo/permissions
if command -v sudo >/dev/null 2>&1; then
  sudo chown -R "$WEB_USER:$WEB_GROUP" storage bootstrap/cache || true
else
  chown -R "$WEB_USER:$WEB_GROUP" storage bootstrap/cache 2>/dev/null || true
fi

# Ensure directories (and files) are writable by web + deploy users
chmod -R ug+rwx storage bootstrap/cache || true
find storage bootstrap/cache -type d -exec chmod 775 {} + 2>/dev/null || true
find storage bootstrap/cache -type f -exec chmod 664 {} + 2>/dev/null || true

# Sanity checks for Blade partials that affect styling (avoid shipping a broken head)
if [[ "$NO_VERIFY" != "true" ]]; then
  echo "==> Verifying Blade layout/partials integrity"
  HEAD_PARTIAL="resources/views/partials/head.blade.php"
  LAYOUT="resources/views/layouts/app.blade.php"
  HOME_VIEW="resources/views/home/index.blade.php"
  contains() {
    local needle="$1"; shift
    local file="$1"; shift
    if command -v rg >/dev/null 2>&1; then rg -n -F "$needle" "$file" >/dev/null 2>&1; else grep -n -F "$needle" "$file" >/dev/null 2>&1; fi
  }
  # helper to warn or error based on STRICT_VERIFY
  die_or_warn(){ if [[ "$STRICT_VERIFY" == "true" ]]; then echo "ERROR: $1" >&2; exit 1; else echo "WARN: $1" >&2; fi }

  if [[ ! -f "$HEAD_PARTIAL" ]]; then
    die_or_warn "Missing $HEAD_PARTIAL"
  fi

  # accept multiple Vite forms and quoting (heredoc avoids escaping quotes)
  has_vite="false"
  while IFS= read -r pat; do
    [[ -z "$pat" ]] && continue
    if contains "$pat" "$HEAD_PARTIAL"; then has_vite="true"; break; fi
  done <<'EOF'
@vite('resources/js/app.js')
@vite("resources/js/app.js")
@vite(['resources/js/app.js'])
@vite(["resources/js/app.js"])
EOF
  if [[ "$has_vite" != "true" ]]; then
    die_or_warn "$HEAD_PARTIAL missing a @vite call for resources/js/app.js. Assets may not load."
  fi

  if ! contains "@include('partials.styles')" "$HEAD_PARTIAL"; then
    die_or_warn "$HEAD_PARTIAL missing @include('partials.styles'). Base CSS may not apply."
  fi
  if ! contains "@inertiaHead" "$HEAD_PARTIAL"; then
    echo "WARN: $HEAD_PARTIAL missing @inertiaHead (Inertia page head won't hydrate)." >&2
  fi
  if ! contains "@include('partials.head')" "$LAYOUT"; then
    die_or_warn "$LAYOUT missing @include('partials.head') in <head>."
  fi
  if ! contains "<main" "$LAYOUT" || ! contains "@yield('content')" "$LAYOUT"; then
    die_or_warn "$LAYOUT should include a <main> with @yield('content')."
  fi
  if [[ -f "$HOME_VIEW" ]] && ! contains "@extends('layouts.app')" "$HOME_VIEW"; then
    echo "WARN: $HOME_VIEW does not extend the app layout." >&2
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
