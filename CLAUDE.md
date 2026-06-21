# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Dompet Patungan** â a group expense splitting and settlement management app. Indonesian-language domain: managing shared expenses ("patungan"), bill splitting, and debt settlement among group members.

## Tech Stack

- **Backend:** Laravel 13.7 (PHP 8.3+), Fortify (session-based auth), Inertia.js 3.0
- **Frontend:** Vue 3.5 + TypeScript, Tailwind CSS 4.1, Vite 8
- **Database:** PostgreSQL (production) / SQLite (local dev & testing)
- **UI:** Reka-UI component primitives, Lucide Vue icons, Vue Sonner toasts
- **Testing:** Pest PHP 4.7, Larastan (PHPStan level 7), ESLint, Prettier, vue-tsc

## Commands

```bash
# Full setup
composer setup

# Development (server + queue + vite, runs concurrently)
composer dev

# PHP - lint & fix
composer lint              # pint --parallel
composer lint:check        # pint --parallel --test (dry-run)

# PHP - static analysis
composer types:check       # phpstan analyse

# Run all tests (clears config, lint check, type check, pest)
composer test

# Run a single test file
php artisan test tests/Feature/AdminTest.php

# Run a single test by name filter
php artisan test --filter="system admins can access"

# JavaScript / Vue
npm run dev                # Vite dev server
npm run build              # Production build
npm run lint               # ESLint --fix
npm run lint:check         # ESLint (dry-run)
npm run format             # Prettier --write
npm run format:check       # Prettier --check
npm run types:check        # vue-tsc --noEmit
```

## Architecture

### No Vue Router â Server-Side Routing Only

This app does **not** use Vue Router. All navigation goes through Inertia.js `<Link>` components. Each page is rendered server-side by a Laravel controller returning `Inertia::render('PageName', [...props])`. Layout resolution is determined by page name in `resources/js/app.ts`:

- `Welcome` â no layout
- `auth/*` â `AuthLayout`
- `settings/*` â `AppLayout` + `SettingsLayout`
- Everything else â `AppLayout`

### Shared Data (Global Props)

`app/Http/Middleware/HandleInertiaRequests.php` injects shared props into every Vue component: `auth.user`, `name` (app name), and `sidebarOpen`. Flash messages use Vue Sonner via `resources/js/lib/flashToast.ts`.

### Authentication & Authorization

Session-based auth via Laravel Fortify. Three middleware guard routes:

- `auth` â standard Laravel authentication
- `active` â `EnsureUserIsActive`: logs out and redirects to login if `user.is_active === false`
- `system.admin` â `EnsureSystemAdmin`: aborts 403 unless `user.role === 'system_admin'`

Users have two role-related fields: `role` (string: `'user'` or `'system_admin'`) and `is_active` (boolean). System admins are platform overseers, NOT group admins â they don't manage individual groups, only aggregate monitoring and user account control.

### Route Structure

| Prefix | Middleware | Purpose |
|--------|-----------|---------|
| `/` | guest | Welcome page, auth views (Fortify renders Inertia) |
| `/dashboard`, `/groups`, `/payments`, `/settlements` | `auth`, `active`, `verified` | Member features |
| `/admin/*` | `auth`, `active`, `system.admin` | System admin: user management, group monitoring, platform stats |
| `/settings/*` | `auth`, `active` | Profile, password, appearance |

### Dashboard Redirect

`DashboardController` redirects users with `role === 'system_admin'` to `route('admin.stats.index')` instead of showing the user dashboard.

### Data Flow

1. Laravel controller receives request, queries models, returns `Inertia::render('PageName', [data])`
2. Inertia sends props to the Vue page component
3. Forms POST/PATCH to named Laravel routes, which redirect back (with flash messages) or re-render with errors
4. No Axios for page loads â only for isolated interactions if needed

### Database Models & Relationships

- **User** â hasMany Group (via `creator_id`), belongsToMany Group (via `group_members` pivot with `role`), hasMany Payment, hasMany ExpenseSplit
- **Group** â belongsTo User (creator), hasMany GroupMember, hasMany Expense, hasMany Settlement
- **Expense** â belongsTo Group, belongsTo User (payer), hasMany ExpenseSplit, hasMany Payment
- **ExpenseSplit** â belongsTo Expense, belongsTo User (the debtor)
- **Payment** â belongsTo Expense, belongsTo User; status: `pending`, `confirmed`, `rejected`
- **Settlement** â belongsTo Group, belongsTo User (generated_by); `debt_details` is JSONB

### Form Requests (Validation)

Located in `app/Http/Requests/`. Backend validation is centralized in Form Request classes. Key ones: `StoreGroupRequest`, `StoreExpenseRequest`, `StorePaymentRequest`, `UpdateUserStatusRequest`, `RejectPaymentRequest`.

### Frontend Component Organization

- `resources/js/pages/` â full-page components, one per route (e.g., `auth/Login.vue`, `groups/Show.vue`, `admin/Stats.vue`)
- `resources/js/components/` â reusable UI pieces (e.g., `AppHeader.vue`, `AppSidebar.vue`, `AdminNav.vue`, `NotificationList.vue`)
- `resources/js/components/ui/` â Reka-UI based primitives (button, card, dialog, dropdown-menu, select, sheet, sidebar, etc.)
- `resources/js/layouts/` â layout wrappers: `AppLayout.vue`, `AuthLayout.vue`, `settings/Layout.vue`
- `resources/js/composables/` â Vue composables: `useAppearance`, `useCurrentUrl`, `useInitials`
- `resources/js/lib/` â utilities: `flashToast.ts`, `utils.ts`

### Design System â Verdana Health

The UI follows a calm, clinical health-theme design system:

- **Fonts:** Plus Jakarta Sans (headings), DM Sans (body), Fira Code (mono/numbers)
- **Colors:** Primary Navy (#0F172A), Sage (#059669) for CTAs/highlights, Slate (#64748B) for secondary text
- **Border radius:** 8px default (softer, approachable), 4px for badges/chips
- **Shadows:** Gentle diffused â no heavy drop shadows
- Full spec in `Docs/DESIGN.md`

### Uncommitted Changes

The working tree currently has changes across admin controllers, Vue components for admin pages (Stats, Users, Groups), and related tests. See `git diff --stat` for details.

## Testing Conventions

- Uses Pest PHP with `RefreshDatabase` trait (auto-applied in `tests/Pest.php`)
- Factories are in `database/factories/`
- Test files mirror the feature area: `tests/Feature/AdminTest.php`, `tests/Feature/GroupsTest.php`, etc.
- Admin tests verify: role-based access control, system admin redirect from dashboard, user deactivation/reactivation, and aggregate stats privacy (no private expense titles exposed)