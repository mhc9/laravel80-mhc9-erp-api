# MHC9-ERP

Laravel 8 ERP application for a Thai government entity. Bilingual Thai/English.

## Modules

- Asset management (hardware, software/computer sets, repairations)
- Budget management (plans, projects, activities, allocations)
- Procurement/Requisition workflow with approval chains
- Employee/Department/Division management
- Attendance/WPM integration (face recognition, check-in/check-out)
- Loan contracts and loan refunds
- Inspection management
- Leave management
- Report generation (PDF, Excel, Word)

## Stack

- **Backend**: Laravel 8.75, PHP 8.1, MySQL (primary) + MSSQL (legacy/secondary)
- **Frontend**: React 17 + Redux Toolkit + React Router v6 + Bootstrap 5
- **Auth**: JWT via `tymon/jwt-auth` (default `api` guard), API key middleware for some routes
- **Build**: Laravel Mix 6 (webpack)
- **PDF**: DomPDF
- **Excel**: Maatwebsite Excel + PHPSpreadsheet
- **LINE Notify**: `phattarachai/line-notify`

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run dev
php artisan serve
```

## Commands

```bash
php artisan serve          # Dev server
php artisan route:list     # Inspect API routes
npm run dev                # Build frontend
npm run watch              # Watch mode for frontend
composer dump-autoload     # After creating new classes
```

## Architecture

Service-Repository pattern (partially adopted):

```
Controllers -> Services -> Repositories -> Eloquent Models
```

- `app/Repositories/BaseRepository.php` — abstract CRUD base
- `app/Services/BaseService.php` — abstract service base
- Some controllers bypass services and use Eloquent directly
- `app/helpers.php` — auto-loaded global helpers (Thai date conversion, PDF rendering, baht text, file uploads)

## Key Conventions

- All API routes prefixed with `/api`
- Most routes use `auth:api` (JWT); some use `api.key` (X-API-KEY header)
- No custom migrations — DB schema managed outside Laravel
- Thai Buddhist Era dates: use `convDbDateToThDate()` / `convThDateToDbDate()` (+543 years)
- Fiscal year: Oct–Sep, use `calcBudgetYear()` helper
- File uploads: use `FileUploader` trait or `uploadFile()` / `uploadThumbnail()` helpers
- Many models have `$guarded = []` (mass assignment open)

## Frontend

- Entry: `resources/js/app.js`, `resources/sass/app.scss`
- Uses `HashRouter` (not BrowserRouter)
- Redux slices in `resources/js/slices/`

## License

Proprietary — Thai government entity.
