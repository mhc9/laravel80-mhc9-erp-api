# AGENTS.md

## Project

Laravel 8 ERP app (MHC9-ERP) for a Thai government entity. Bilingual Thai/English. Manages assets, budgets, procurement, employees, attendance, loans, inspections, and more.

## Stack

- **Backend**: Laravel 8.75, PHP 8.1, MySQL (primary) + MSSQL (legacy/secondary)
- **Frontend**: React 17 + Redux Toolkit + React Router v6 + Bootstrap 5
- **Auth**: JWT via `tymon/jwt-auth` (default `api` guard), API key middleware for some routes
- **Build**: Laravel Mix 6 (webpack) — `npm run dev` / `npm run prod`
- **PDF**: DomPDF (`barryvdh/laravel-dompdf`)
- **Excel**: Maatwebsite Excel + PHPSpreadsheet
- **LINE Notify**: `phattarachai/line-notify`

## Commands

```bash
# No custom scripts defined beyond Laravel defaults
php artisan serve          # Dev server
php artisan route:list     # Inspect API routes
npm run dev                # Build frontend (webpack mix)
npm run watch              # Watch mode for frontend
composer dump-autoload     # After creating new classes in app/
```

## Architecture

**Service-Repository pattern** (partially adopted):

```
Controllers → Services → Repositories → Eloquent Models
```

- `app/Repositories/BaseRepository.php` — abstract CRUD base
- `app/Services/BaseService.php` — abstract service base
- Some controllers bypass services and use Eloquent directly (e.g., TaskController, some simpler CRUD)
- `app/helpers.php` — auto-loaded global helpers (Thai date conversion, PDF rendering, baht text, file uploads)

## Key Conventions

- **Route prefix**: All API routes under `/api` (auto-prefixed by RouteServiceProvider)
- **Auth middleware**: Most routes use `auth:api` (JWT). Some use `api.key` (X-API-KEY header)
- **No custom migrations**: DB schema managed outside Laravel (likely directly on MSSQL/MySQL)
- **Thai Buddhist Era dates**: Use `convDbDateToThDate()` / `convThDateToDbDate()` helpers (+543 years)
- **Fiscal year**: Oct–Sep, use `calcBudgetYear()` helper
- **File uploads**: Use `FileUploader` trait or `uploadFile()` / `uploadThumbnail()` helpers from `app/helpers.php`
- **Models have `$guarded = []`** on many tables (mass assignment open) — be cautious with `update()` calls

## Frontend

- Entry: `resources/js/app.js` (React), `resources/sass/app.scss`
- Uses `HashRouter` (not BrowserRouter)
- Redux slices in `resources/js/slices/`
- API calls likely via axios (check `resources/js/` for API service files)

## Gotchas

- No static analysis (phpstan) or code style fixer (php-cs-fixer) configured
- No test suite beyond default Laravel stubs — do not rely on tests for verification
- `.github/` is gitignored — no CI workflows
- `composer.lock` is tracked — always commit it with dependency changes
- MSSQL connection configured but most code targets MySQL — check `config/database.php` for active connection
