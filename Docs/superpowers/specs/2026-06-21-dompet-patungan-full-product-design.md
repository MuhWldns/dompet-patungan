# Dompet Patungan Full Product Design

## Goal

Implement the functional scope in `Docs/ABOUT_PROJECT.md` as a full Laravel + Vue/Inertia product pass, using MySQL for both runtime and automated tests.

## Approach

Build the product in thin vertical slices instead of one large unverified change. Each slice should include database changes, domain logic, authorization, Inertia UI, and focused tests where applicable.

## Architecture

- Keep the app as Laravel web routes returning Inertia pages; do not introduce Vue Router or a separate JSON API for normal page loading.
- Use MySQL as the default local/runtime database in `.env.example`.
- Use a separate MySQL database in `phpunit.xml` for automated tests.
- Store receipt and payment proof uploads on Laravel's local `public` disk for the first implementation.
- Use Form Requests for validation, Eloquent relationships for domain traversal, policies or middleware for access control, and flash/Inertia validation errors for feedback.

## Data Model

- `users`: preserve existing auth fields, add `role` (`user`, `system_admin`) and `is_active`.
- `groups`: UUID primary key, creator, name, description, target amount, status (`active`, `settled`, `closed`), invite token.
- `group_members`: group/user pivot with role (`admin`, `member`) and unique membership.
- `expenses`: UUID primary key, group, payer/admin user, title, amount, category, date, optional receipt path, status (`pending`, `locked`).
- `expense_splits`: expense/user owed amounts generated from equal or custom split rules.
- `payments`: UUID primary key, expense, user, amount, method (`transfer`, `cash`, `qris`), optional proof path, status (`pending`, `confirmed`, `rejected`), optional rejection reason.
- `settlements`: UUID primary key, group, generated-by user, JSON debt details, generated timestamp.
- `notifications`: user, message, type, optional link, read timestamp.

`expense_splits` is required even though the source spec does not list it explicitly, because custom split and generated per-member bills need a durable per-user amount to validate payments and settlement logic.

## Routes And UI

Implement these as Inertia web pages and form actions:

- `/groups`: list memberships and create groups.
- `/groups/{group}`: group detail with members, expenses, payment status, invite link, and settlement summary.
- `/groups/{group}/join/{token}`: join by invite token.
- `/groups/{group}/expenses`: create expenses with equal/custom splits and optional receipt upload.
- `/payments`: show current user's generated bills across groups.
- `/payments/{payment}/pay`: submit payment method and optional proof upload.
- `/settlements/{group}`: show generated settlement output.
- `/admin/users`: system-admin user management with activate/deactivate actions.
- `/admin/stats`: aggregate platform statistics without private expense details.

Group admins can invite members, add expenses, confirm/reject payments, and generate settlements. Members can view their groups, view bills, submit payments, and view settlements for their groups.

The UI should be practical and complete: dashboard summaries, list/table views, forms, status badges, upload inputs, notifications, and basic empty states. Follow `Docs/DESIGN.md` for visible product surfaces: dark/white full-bleed rhythm where suitable, true black, scarce cobalt, pill actions, and no card shadows.

## Authorization

- Authenticated users can only access groups where they are members.
- Group admin actions require membership role `admin`.
- System-admin routes require `role = system_admin`.
- Deactivated users are blocked from normal authenticated app access after login/session checks.
- System admins may view aggregate platform stats and manage users, but should not receive private expense details in admin stats views.

## Validation And Errors

- Validate group, expense, payment, and admin actions with Laravel Form Requests.
- Expense split totals must equal the expense amount exactly.
- Payment submissions must match the current user's generated bill and allowed payment methods.
- Uploads should accept common receipt/proof file types and store paths on the public disk.
- Rejected payments may be resubmitted; rejection reason is optional but persisted when present.
- Use Inertia validation errors and flash messages for user feedback.

## Settlement Logic

Settlement generation computes net balances per group from confirmed payments and generated expense splits, then produces a minimal set of debtor-to-creditor transfers. Persist the generated output as JSON on `settlements.debt_details` so all group members can view the same result.

## Testing

Keep automated tests on a dedicated MySQL database via `phpunit.xml`; runtime defaults also use MySQL.

Add focused Pest coverage for:

- User roles and blocked/deactivated users.
- Group creation, membership, invite join, and member visibility.
- Expense creation with equal and custom splits.
- Payment submission, local upload handling, confirm/reject flow, and resubmission after rejection.
- Settlement netting correctness with multiple users.
- System-admin user management and aggregate stats access.
- Basic Inertia page availability for the new screens.

## Verification

Run these before claiming implementation completion:

- `composer lint:check`
- `composer types:check`
- `php artisan test`
- `npm run lint:check`
- `npm run format:check`
- `npm run types:check`
- `npm run build`
