# AGENTS.md

## Project Shape

- This is a Laravel 13 app using PHP `^8.3`, Vue 3, Inertia v3, Tailwind CSS v4, Fortify, Pest 4, Larastan, and Laravel Wayfinder.
- Runtime routes are web/Inertia routes, not a separate API: `bootstrap/app.php` wires `routes/web.php`, and `routes/web.php` includes `routes/settings.php`.
- The product spec is in `Docs/ABOUT_PROJECT.md`; implemented domain routes now cover groups, expenses/splits, payments, settlements, notifications, and system-admin aggregate/user management.
- The visual brief is `Docs/DESIGN.md`: dark/white full-bleed band rhythm, true black `#000000`, scarce cobalt `#494fdf`, pill buttons, no card shadows. Current CSS still uses the starter shadcn/Tailwind token set.

## Commands

- Full setup: `composer setup` runs Composer install, creates `.env`, generates `APP_KEY`, migrates, installs npm packages, then builds assets.
- Local dev: `composer dev` runs `php artisan serve`, `php artisan queue:listen --tries=1`, and `npm run dev` concurrently.
- Backend format: `composer lint`; check only with `composer lint:check`.
- Frontend format: `npm run format`; check only with `npm run format:check`.
- Frontend lint: `npm run lint`; check only with `npm run lint:check`.
- Type checks are split: `composer types:check` for Larastan level 7 over `app/`, `bootstrap/app.php`, `config/`, `database/`, `routes/`; `npm run types:check` for `vue-tsc --noEmit`.
- Composer `test` is intentionally broad: it clears config, runs Pint check, Larastan, then `php artisan test`.
- Composer `ci:check` runs frontend lint check, frontend format check, frontend type check, then Composer `test`.

## Testing Notes

- Focused PHP tests can be run with `php artisan test --filter=Name` or a path like `php artisan test tests/Feature/DashboardTest.php`.
- PHPUnit uses a MySQL test database via `phpunit.xml` (`dompet_patungan_test`); Feature tests get `RefreshDatabase` automatically from `tests/Pest.php`.
- Do not run multiple `php artisan test ...` processes in parallel against the same MySQL test database; migrations can collide and deadlock. Run focused suites sequentially.
- CI tests install Node and Composer dependencies, copy `.env.example`, run `php artisan key:generate`, build assets, run `composer types:check`, then `php artisan test`.
- CI lint is mutating: `.github/workflows/lint.yml` runs `composer lint`, `npm run format`, and `npm run lint`, not check-only variants.

## Frontend Conventions

- Inertia pages live under `resources/js/pages`; page names are lowercase where routes/Fortify reference them, e.g. `auth/Login`, `settings/Appearance`.
- `resources/js/app.ts` assigns layouts by page name: `Welcome` has no layout, `auth/*` uses `AuthLayout`, `settings/*` nests `[AppLayout, SettingsLayout]`, everything else uses `AppLayout`.
- Product Inertia pages use lowercase folders: `groups/Index`, `groups/Show`, `payments/Index`, `settlements/Show`, `admin/Users`, `admin/Stats`.
- Use Inertia navigation/forms instead of Vue Router or Axios page-loading patterns; shared props currently include `name`, `auth.user`, and `sidebarOpen` from `HandleInertiaRequests`.
- TypeScript alias `@/*` maps to `resources/js/*`.
- Wayfinder is enabled in `vite.config.ts` with `formVariants: true`; generated files under `resources/js/actions/**`, `resources/js/routes/**`, and `resources/js/wayfinder/**` are lint-ignored, so do not hand-edit them.
- shadcn-vue config uses `new-york-v4`, aliases `@/components`, `@/components/ui`, `@/composables`, `@/lib`, and Lucide icons.
- Prettier uses 4-space tabs, single quotes, semicolons, print width 80, and `prettier-plugin-tailwindcss` with class sorting for `clsx`, `cn`, and `cva`.
- ESLint requires top-level type imports, alphabetical import ordering by group, braces for all control statements, and blank lines around control statements.

## Backend Conventions

- Fortify renders Inertia auth pages from `app/Providers/FortifyServiceProvider.php`; login, forgot-password, and reset-password UI are not normal controller routes.
- Fortify login throttling is `5/minute` by lowercase username plus IP; password update route in `routes/settings.php` is additionally `throttle:6,1`.
- `bootstrap/app.php` exempts `appearance` and `sidebar_state` cookies from encryption and appends `HandleAppearance`, `HandleInertiaRequests`, and asset preload link middleware to the web stack.
- PHP style is Laravel Pint preset only (`pint.json`); avoid adding custom style rules unless the repo config changes.
