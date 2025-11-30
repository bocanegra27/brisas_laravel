<!-- Copilot / AI agent instructions for brisas_laravel -->
# Quick context

- Project: Laravel 12 application (PHP >= 8.2) with Vite + Tailwind for frontend assets.
- Tests: Pest (Pest plugin for Laravel) — tests live in `tests/Feature` and `tests/Unit`.

# What matters for an AI coding agent

- Primary web entry: `routes/web.php` — e.g., `Route::get('/', [HomeController::class, 'index'])`.
- Views: `resources/views/` (Blade templates). Example: `resources/views/index.blade.php`.
- Controllers: `app/Http/Controllers/` (look for `HomeController`).
- Models: `app/Models/` (e.g., `User.php` uses `$fillable` and `casts()` patterns).
- Database seeds & factories: `database/seeders/`, `database/factories/`.

# Build / Dev / Test workflows (commands)

- Install + setup (recommended):
```powershell
composer run-script setup
```
This runs composer install, copies `.env`, `php artisan key:generate`, migrations, `npm install` and `npm run build` per `composer.json`.

- Run the local dev stack (concurrent server + queue + vite):
```powershell
composer run-script dev
```
Note: On Windows this uses `npx concurrently` as configured in `composer.json`.

- Frontend only (Vite):
```powershell
npm run dev   # for HMR during development
npm run build # production build
```

- Run tests:
```powershell
./vendor/bin/pest      # or `vendor\bin\pest.bat` on Windows
composer test          # runs `@php artisan test` as configured
```

- Common artisan tasks:
```powershell
php artisan key:generate
php artisan migrate --force
php artisan db:seed
php artisan storage:link
```

# Project-specific conventions & patterns

- Testing uses an in-memory sqlite configuration (see `phpunit.xml`) — test DB isolation is enabled.
- `User` model uses `protected $fillable` and `casts()` (see `app/Models/User.php`) — prefer using Eloquent patterns and factories.
- Asset inputs are `resources/css/app.css` and `resources/js/app.js` configured in `vite.config.js`.
- Composer scripts include lifecycle hooks (`post-create-project-cmd`, `post-update-cmd`) — be cautious when editing them.

# Where to look for changes / integration points

- Routes: `routes/web.php` and other `routes/*.php` files.
- Controllers → Views: `app/Http/Controllers/*` maps to `resources/views/*` (Blade templates).
- Frontend entry: `resources/js/app.js` and Tailwind pipeline in `package.json` + `vite.config.js`.
- Database migrations: `database/migrations/` (use `php artisan migrate` to apply).

# Safety notes for automated edits

- Preserve `composer.json` scripts and `phpunit.xml` test settings unless explicitly asked to change build/test behavior.
- Avoid changing default environment handling — `.env` is expected to be copied from `.env.example` by setup scripts.
- Do not run destructive DB operations in CI or dev branches without an explicit migration plan.

# If you need more context

- Open `routes/web.php`, `app/Http/Controllers/HomeController.php`, `resources/views/index.blade.php`, `package.json`, `composer.json`, and `phpunit.xml` for concrete examples used across the app.
- Ask the human for missing credentials or intended deployment workflow (e.g., Docker, Sail, or host).

---
Please review this guidance and tell me any areas you'd like expanded (CI steps, deployment, or coding conventions).
