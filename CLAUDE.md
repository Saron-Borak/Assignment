# EAMU Student Attendance Management System

Laravel 13 + MySQL. Blade views with Bootstrap 5 from a CDN — **there is no asset
build step**; do not add Vite, npm or a `package.json`.

## Conventions

- **Models** use PHP attributes (`#[Fillable([...])]`, `#[Hidden([...])]`), not
  `$fillable` / `$hidden` properties. This is the Laravel 13 idiom.
- **Enums** in `app/Enums/` are backed string enums, cast on the models. Add
  presentation helpers (`label()`, `badgeClass()`, `icon()`) there rather than in
  views.
- **All attendance writes** go through `App\Services\AttendanceService`; **all
  attendance reads** through `App\Services\AttendanceReportService`. Never write to
  `attendance_records` from a controller — the manual register, the QR kiosk and
  the typed-code fallback must stay in step.
- **Reports aggregate in SQL.** A roster summary is one grouped query with
  conditional `SUM(...)`. Never loop students and query per student.
- **Authorization is two layers**: `role` middleware gates the portal, a policy
  proves ownership. Adding a lecturer or student route requires both.
- **Ordering by a related column** uses an ordering subquery
  (`->orderBy(User::select('name')->whereColumn(...))`), not a join. A join forces
  `select('table.*')`, which silently discards `withCount` subqueries and leaves
  bare column names ambiguous.
- **QR codes are SVG only** (`App\Support\QrRenderer`). `ext-gd` and `ext-imagick`
  are not assumed to be present.

## Commands

```bash
php artisan migrate:fresh --seed   # rebuild the demo database
php artisan test                   # 67 tests, in-memory SQLite
php artisan serve --host=0.0.0.0   # needed for phone QR scanning
```

Tests run with `Model::preventLazyLoading()` enabled, so a missing eager-load
fails the suite rather than silently costing queries.

See `README.md` for setup, credentials and the schema.
