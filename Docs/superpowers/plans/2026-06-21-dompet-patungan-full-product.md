# Dompet Patungan Full Product Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the `Docs/ABOUT_PROJECT.md` product as a Laravel + Vue/Inertia app with MySQL runtime defaults and SQLite automated tests.

**Architecture:** Keep normal runtime as Laravel web routes returning Inertia pages. Build in vertical slices: schema/domain, access control, groups, expenses/splits, payments/uploads, settlements, notifications, system admin, and dashboard/UI polish.

**Tech Stack:** Laravel 13, Fortify, Inertia v3, Vue 3, Tailwind CSS v4, Wayfinder, Pest 4, Larastan, MySQL runtime, SQLite tests.

---

## File Structure

- Modify `.env.example`: switch runtime DB defaults to MySQL.
- Modify `AGENTS.md`: note the new domain routes/models after implementation if conventions change.
- Modify `database/migrations/0001_01_01_000000_create_users_table.php`: add `role` and `is_active` to users.
- Create domain migrations in `database/migrations/`: `groups`, `group_members`, `expenses`, `expense_splits`, `payments`, `settlements`, `notifications`.
- Modify `app/Models/User.php`: add role/active casts, relationships, and authorization helpers.
- Create models under `app/Models/`: `Group`, `GroupMember`, `Expense`, `ExpenseSplit`, `Payment`, `Settlement`, `Notification`.
- Create form requests under `app/Http/Requests/`: `StoreGroupRequest`, `StoreExpenseRequest`, `StorePaymentRequest`, `RejectPaymentRequest`, `UpdateUserStatusRequest`.
- Create middleware under `app/Http/Middleware/`: `EnsureUserIsActive`, `EnsureSystemAdmin`.
- Modify `bootstrap/app.php`: alias and/or append middleware needed for active-user checks and system-admin routes.
- Create controllers under `app/Http/Controllers/`: `DashboardController`, `GroupController`, `GroupExpenseController`, `PaymentController`, `SettlementController`, `NotificationController`, `Admin/UserController`, `Admin/StatsController`.
- Modify `routes/web.php`: replace starter-only routes with product routes while preserving settings routes.
- Create domain services under `app/Services/`: `ExpenseSplitService`, `SettlementService`, `NotificationService`.
- Create Vue pages under `resources/js/pages/`: `Dashboard.vue`, `groups/Index.vue`, `groups/Show.vue`, `payments/Index.vue`, `settlements/Show.vue`, `admin/Users.vue`, `admin/Stats.vue`.
- Create or update reusable Vue components under `resources/js/components/`: status badges, form errors, amount display, upload field, and notification list.
- Add Pest tests under `tests/Feature/`: `GroupsTest.php`, `ExpensesTest.php`, `PaymentsTest.php`, `SettlementsTest.php`, `AdminTest.php`, `DashboardTest.php`.

## Task 1: Runtime Database Defaults

**Files:**
- Modify: `.env.example`
- Test: manual config inspection

- [ ] **Step 1: Update MySQL defaults**

Change `.env.example` DB section to:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dompet_patungan
DB_USERNAME=root
DB_PASSWORD=
```

- [ ] **Step 2: Confirm tests still use SQLite**

Run: `php artisan test --filter=ExampleTest`

Expected: PASS; `phpunit.xml` still contains `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`.

- [ ] **Step 3: Commit**

Run:

```bash
git add .env.example
git commit -m "chore: default local database to mysql"
```

## Task 2: Domain Schema And Models

**Files:**
- Modify: `database/migrations/0001_01_01_000000_create_users_table.php`
- Modify: `app/Models/User.php`
- Create: domain migrations listed in File Structure
- Create: domain models listed in File Structure
- Test: `tests/Feature/DomainSchemaTest.php`

- [ ] **Step 1: Write schema tests**

Create `tests/Feature/DomainSchemaTest.php` with tests asserting tables and key columns exist after migration:

```php
<?php

use Illuminate\Support\Facades\Schema;

test('domain tables and user role fields exist', function () {
    expect(Schema::hasColumns('users', ['role', 'is_active']))->toBeTrue();
    expect(Schema::hasTable('groups'))->toBeTrue();
    expect(Schema::hasTable('group_members'))->toBeTrue();
    expect(Schema::hasTable('expenses'))->toBeTrue();
    expect(Schema::hasTable('expense_splits'))->toBeTrue();
    expect(Schema::hasTable('payments'))->toBeTrue();
    expect(Schema::hasTable('settlements'))->toBeTrue();
    expect(Schema::hasTable('notifications'))->toBeTrue();
});
```

- [ ] **Step 2: Run failing test**

Run: `php artisan test tests/Feature/DomainSchemaTest.php`

Expected: FAIL because domain tables/columns do not exist yet.

- [ ] **Step 3: Implement migrations**

Add user fields and create domain tables using portable Laravel column types. Use `uuid('id')->primary()` for spec UUID tables, `foreignUuid()` for UUID FKs, `foreignId()` for `users`, `decimal('amount', 12, 2)` for money, `json('debt_details')` for settlements, unique indexes for invite tokens and group membership.

- [ ] **Step 4: Implement models and relationships**

Add fillable/casts and relationships:

```php
// User helpers
public function isSystemAdmin(): bool
{
    return $this->role === 'system_admin';
}

public function isActive(): bool
{
    return (bool) $this->is_active;
}
```

Create relationships for groups, memberships, expenses, splits, payments, settlements, and notifications.

- [ ] **Step 5: Run schema test**

Run: `php artisan test tests/Feature/DomainSchemaTest.php`

Expected: PASS.

- [ ] **Step 6: Commit**

Run:

```bash
git add database/migrations app/Models tests/Feature/DomainSchemaTest.php
git commit -m "feat: add dompet patungan domain schema"
```

## Task 3: Authorization And Active Users

**Files:**
- Create: `app/Http/Middleware/EnsureUserIsActive.php`
- Create: `app/Http/Middleware/EnsureSystemAdmin.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Auth/ActiveUserTest.php`, `tests/Feature/AdminTest.php`

- [ ] **Step 1: Write failing middleware tests**

Test that inactive users are redirected/logged out from `/dashboard`, normal users cannot access `/admin/users`, and system admins can access admin routes.

- [ ] **Step 2: Run tests to verify failure**

Run: `php artisan test tests/Feature/Auth/ActiveUserTest.php tests/Feature/AdminTest.php`

Expected: FAIL because middleware/routes do not exist.

- [ ] **Step 3: Implement middleware**

`EnsureUserIsActive` aborts or logs out inactive authenticated users. `EnsureSystemAdmin` aborts 403 unless `auth()->user()->isSystemAdmin()`.

- [ ] **Step 4: Register middleware and admin route group**

Add aliases in `bootstrap/app.php` and wrap product auth routes with active-user middleware. Add placeholder admin routes returning Inertia pages or controller actions that will be filled in later tasks.

- [ ] **Step 5: Run tests**

Run: `php artisan test tests/Feature/Auth/ActiveUserTest.php tests/Feature/AdminTest.php`

Expected: PASS.

- [ ] **Step 6: Commit**

Run:

```bash
git add app/Http/Middleware bootstrap/app.php routes/web.php tests/Feature/Auth/ActiveUserTest.php tests/Feature/AdminTest.php
git commit -m "feat: enforce active users and system admin access"
```

## Task 4: Groups And Memberships

**Files:**
- Create: `app/Http/Requests/StoreGroupRequest.php`
- Create: `app/Http/Controllers/GroupController.php`
- Modify: `routes/web.php`
- Create: `resources/js/pages/groups/Index.vue`
- Create: `resources/js/pages/groups/Show.vue`
- Test: `tests/Feature/GroupsTest.php`

- [ ] **Step 1: Write failing group tests**

Cover authenticated group creation, creator becomes admin member, invite-token join creates member role, non-members cannot view group details, and members can view details.

- [ ] **Step 2: Run failing tests**

Run: `php artisan test tests/Feature/GroupsTest.php`

Expected: FAIL because controller/routes/pages are absent.

- [ ] **Step 3: Implement request and controller**

Validate `name` required max 255, `description` nullable, `target_amount` nullable numeric min 0. Generate invite token with `Str::random(32)`. Use transactions when creating group plus admin membership.

- [ ] **Step 4: Implement routes and pages**

Add `/groups`, `/groups/{group}`, and `/groups/{group}/join/{token}`. Pages should render lists, create form, members, and invite link.

- [ ] **Step 5: Run group tests and frontend typecheck**

Run: `php artisan test tests/Feature/GroupsTest.php`

Run: `npm run types:check`

Expected: both PASS.

- [ ] **Step 6: Commit**

Run:

```bash
git add app/Http/Requests/StoreGroupRequest.php app/Http/Controllers/GroupController.php routes/web.php resources/js/pages/groups tests/Feature/GroupsTest.php
git commit -m "feat: add groups and memberships"
```

## Task 5: Expenses And Splits

**Files:**
- Create: `app/Services/ExpenseSplitService.php`
- Create: `app/Http/Requests/StoreExpenseRequest.php`
- Create: `app/Http/Controllers/GroupExpenseController.php`
- Modify: `resources/js/pages/groups/Show.vue`
- Test: `tests/Feature/ExpensesTest.php`

- [ ] **Step 1: Write failing expense tests**

Cover admin-only expense creation, equal split generation, custom split exact-total validation, receipt upload to public disk, and member/non-member authorization.

- [ ] **Step 2: Run failing tests**

Run: `php artisan test tests/Feature/ExpensesTest.php`

Expected: FAIL because expense logic is absent.

- [ ] **Step 3: Implement split service**

`ExpenseSplitService::buildEqualSplits(Group $group, string $amount): array` returns per-member decimal strings with remainder cents assigned deterministically. `buildCustomSplits(array $splits, string $amount): array` validates total equals amount.

- [ ] **Step 4: Implement request/controller/UI**

Add expense form supporting `split_method=equal|custom`, category/date/receipt, and split inputs for group members. Store receipt under `receipts` on public disk.

- [ ] **Step 5: Run tests**

Run: `php artisan test tests/Feature/ExpensesTest.php`

Expected: PASS.

- [ ] **Step 6: Commit**

Run:

```bash
git add app/Services/ExpenseSplitService.php app/Http/Requests/StoreExpenseRequest.php app/Http/Controllers/GroupExpenseController.php resources/js/pages/groups/Show.vue tests/Feature/ExpensesTest.php
git commit -m "feat: add expenses and split generation"
```

## Task 6: Payments And Proof Uploads

**Files:**
- Create: `app/Http/Requests/StorePaymentRequest.php`
- Create: `app/Http/Requests/RejectPaymentRequest.php`
- Create: `app/Http/Controllers/PaymentController.php`
- Create: `resources/js/pages/payments/Index.vue`
- Modify: `resources/js/pages/groups/Show.vue`
- Test: `tests/Feature/PaymentsTest.php`

- [ ] **Step 1: Write failing payment tests**

Cover member bill list, payment submission with method/proof, admin confirm, admin reject with reason, and resubmit rejected payment.

- [ ] **Step 2: Run failing tests**

Run: `php artisan test tests/Feature/PaymentsTest.php`

Expected: FAIL because payment logic is absent.

- [ ] **Step 3: Implement payment controller and requests**

Validate `payment_method` in `transfer,cash,qris`, proof nullable file, payment belongs to authenticated user for submit, group admin required for confirm/reject.

- [ ] **Step 4: Implement UI**

Show pending/rejected/confirmed bills, upload field, method selector, and admin confirmation controls in group detail.

- [ ] **Step 5: Run tests and typecheck**

Run: `php artisan test tests/Feature/PaymentsTest.php`

Run: `npm run types:check`

Expected: both PASS.

- [ ] **Step 6: Commit**

Run:

```bash
git add app/Http/Requests/StorePaymentRequest.php app/Http/Requests/RejectPaymentRequest.php app/Http/Controllers/PaymentController.php resources/js/pages/payments resources/js/pages/groups/Show.vue tests/Feature/PaymentsTest.php
git commit -m "feat: add payment submission and review"
```

## Task 7: Settlements

**Files:**
- Create: `app/Services/SettlementService.php`
- Create: `app/Http/Controllers/SettlementController.php`
- Create: `resources/js/pages/settlements/Show.vue`
- Modify: `routes/web.php`
- Test: `tests/Feature/SettlementsTest.php`

- [ ] **Step 1: Write failing settlement tests**

Cover netting for at least three users, admin-only generation, member visibility, and non-member denial.

- [ ] **Step 2: Run failing tests**

Run: `php artisan test tests/Feature/SettlementsTest.php`

Expected: FAIL because settlement logic is absent.

- [ ] **Step 3: Implement settlement service**

Compute balance per member from expense splits and confirmed payments, then greedily match debtors to creditors into transfers with `from_user_id`, `to_user_id`, and `amount`.

- [ ] **Step 4: Implement controller and page**

Persist generated JSON in `settlements.debt_details`. Render latest settlement and transfer list to all group members.

- [ ] **Step 5: Run tests**

Run: `php artisan test tests/Feature/SettlementsTest.php`

Expected: PASS.

- [ ] **Step 6: Commit**

Run:

```bash
git add app/Services/SettlementService.php app/Http/Controllers/SettlementController.php resources/js/pages/settlements routes/web.php tests/Feature/SettlementsTest.php
git commit -m "feat: add settlement generation"
```

## Task 8: Notifications And Dashboard

**Files:**
- Create: `app/Services/NotificationService.php`
- Create: `app/Http/Controllers/NotificationController.php`
- Create/Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `resources/js/pages/Dashboard.vue`
- Create: `resources/js/components/NotificationList.vue`
- Test: `tests/Feature/DashboardTest.php`

- [ ] **Step 1: Write failing dashboard/notification tests**

Cover dashboard loads summaries, expense creation/payment review creates notifications, and users can mark notifications read.

- [ ] **Step 2: Run failing tests**

Run: `php artisan test tests/Feature/DashboardTest.php`

Expected: FAIL for missing notification/dashboard behavior.

- [ ] **Step 3: Implement services/controllers**

Create notification records for new bills, payment submitted, payment confirmed/rejected, and settlement generated. Dashboard returns group count, pending bills, recent notifications, and recent groups.

- [ ] **Step 4: Implement UI**

Render summary bands, pending payment links, recent groups, and notification list with read action.

- [ ] **Step 5: Run tests**

Run: `php artisan test tests/Feature/DashboardTest.php`

Expected: PASS.

- [ ] **Step 6: Commit**

Run:

```bash
git add app/Services/NotificationService.php app/Http/Controllers/NotificationController.php app/Http/Controllers/DashboardController.php resources/js/pages/Dashboard.vue resources/js/components/NotificationList.vue tests/Feature/DashboardTest.php
git commit -m "feat: add dashboard notifications"
```

## Task 9: System Admin

**Files:**
- Create: `app/Http/Requests/UpdateUserStatusRequest.php`
- Create: `app/Http/Controllers/Admin/UserController.php`
- Create: `app/Http/Controllers/Admin/StatsController.php`
- Create: `resources/js/pages/admin/Users.vue`
- Create: `resources/js/pages/admin/Stats.vue`
- Test: `tests/Feature/AdminTest.php`

- [ ] **Step 1: Extend failing admin tests**

Cover system admin can list users, deactivate/reactivate users, view aggregate stats, and normal users receive 403. Assert stats response does not include private expense titles.

- [ ] **Step 2: Run failing admin tests**

Run: `php artisan test tests/Feature/AdminTest.php`

Expected: FAIL until controllers/pages are complete.

- [ ] **Step 3: Implement admin controllers and request**

Allow updating `is_active` except prevent deactivating the current admin user. Stats include counts and totals only.

- [ ] **Step 4: Implement admin pages**

Users page lists name/email/role/status/actions. Stats page shows aggregate counts and total confirmed payments/expenses without item details.

- [ ] **Step 5: Run tests and typecheck**

Run: `php artisan test tests/Feature/AdminTest.php`

Run: `npm run types:check`

Expected: both PASS.

- [ ] **Step 6: Commit**

Run:

```bash
git add app/Http/Requests/UpdateUserStatusRequest.php app/Http/Controllers/Admin resources/js/pages/admin tests/Feature/AdminTest.php
git commit -m "feat: add system admin management"
```

## Task 10: Final UI Integration And Verification

**Files:**
- Modify: `resources/js/components/*`, `resources/js/layouts/*`, `resources/js/pages/*`
- Modify: `AGENTS.md` if new conventions need documenting

- [ ] **Step 1: Polish navigation and visual consistency**

Ensure app navigation links include Dashboard, Groups, Payments, Admin when relevant, and settings. Apply `Docs/DESIGN.md` guidance where practical: true black sections, white sections, pill buttons, scarce cobalt, no card shadows.

- [ ] **Step 2: Run backend verification**

Run: `composer lint:check`

Expected: PASS.

Run: `composer types:check`

Expected: PASS.

Run: `php artisan test`

Expected: PASS.

- [ ] **Step 3: Run frontend verification**

Run: `npm run lint:check`

Expected: PASS.

Run: `npm run format:check`

Expected: PASS.

Run: `npm run types:check`

Expected: PASS.

Run: `npm run build`

Expected: PASS.

- [ ] **Step 4: Commit final integration**

Run:

```bash
git add AGENTS.md resources/js app routes database tests .env.example
git commit -m "feat: complete dompet patungan product pass"
```

## Self-Review

- Spec coverage: plan covers MySQL runtime defaults, SQLite tests, domain schema, group/member flow, expenses/splits, payments/uploads, settlements, notifications, system admin, Inertia UI, authorization, validation, and final verification.
- Placeholder scan: no `TBD`, `TODO`, or undefined future-only sections are used as implementation instructions.
- Type consistency: model, service, controller, route, and page names are consistent across tasks.
