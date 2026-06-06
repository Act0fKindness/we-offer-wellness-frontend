# Agent Rules

- Proceed carefully.
- No DB work ever.
- No unit tests ever.
- After changes, run `npm install` (if dependencies changed) and `npm run build`.
- Do not use Laravel Mix; use the Vite-based build (`npm run build`) only.
- After changes that affect runtime behavior, run `php artisan optimize:clear`.
