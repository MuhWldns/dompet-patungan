# Design: Group Member Management & Enhanced Notifications

**Date:** 2026-06-22
**Status:** Approved

## Feature 1: Remove Member & Change Group Status

### Routes

| Method | Route | Controller | Middleware |
|--------|-------|-----------|------------|
| `DELETE` | `/groups/{group}/members/{member}` | `GroupMemberController@destroy` | `auth`, `active`, `verified` |
| `PATCH` | `/groups/{group}/status` | `GroupController@updateStatus` | `auth`, `active`, `verified` |

### `GroupMemberController@destroy`

New controller: `app/Http/Controllers/GroupMemberController.php`.

Logic:
1. Abort 403 if user is not a member of the group
2. Determine intent: `$self = $request->user()->is($member)` â self = leave, other = kick
3. If kick (other): abort 403 unless user is group admin
4. If the member being removed is the ONLY admin in the group, promote the oldest remaining member (by `group_members.created_at`) to admin. If there are other admins, no promotion needed
5. If the member being removed is the last member, auto-close the group (status = `closed`)
6. Detach the member from the group
7. Send notification:
   - If kicked: `member.removed` to the kicked member
   - If left: `member.left` to all remaining group members

### `GroupController@updateStatus`

Add method to existing `GroupController`:

1. Abort 403 if user is not group admin
2. Validate: `status` must be `'settled'` or `'closed'`
3. If current status is `'closed'`, abort 422 (cannot reopen)
4. Update group status
5. Send `group.status_changed` notification to all group members

### Frontend: `groups/Show.vue`

Add to member list:
- Delete button (ÃÂ icon) on each member card
- Admin sees delete button on all members
- Member sees delete button only on their own card (as "Leave")
- Confirmation dialog (Inertia Link with `method="delete"`)

Add to hero/admin section:
- Status change dropdown/toggle: active â settled â closed
- Inertia Link with `method="patch"` to `/groups/{group}/status`

---

## Feature 2: Enhanced Notifications

### Backend

#### New Routes

| Method | Route | Controller | Middleware |
|--------|-------|-----------|------------|
| `GET` | `/notifications/unread-count` | `NotificationController@unreadCount` | `auth`, `active`, `verified` |
| `PATCH` | `/notifications/mark-all-read` | `NotificationController@markAllRead` | `auth`, `active`, `verified` |

#### `NotificationController` Changes

Add methods:
- `unreadCount()` â return `{ count: N }` JSON for polling
- `markAllRead()` â set `read_at = now()` for all unread notifications of the authenticated user, redirect back

#### `NotificationController@index` (enhance existing)

Support query param `?filter=unread|<type>`:
- `unread` â only notifications where `read_at IS NULL`
- `bill.created`, `payment.submitted`, `payment.confirmed`, `payment.rejected`, `payment.overdue`, `settlement.generated`, `member.removed`, `member.left`, `group.status_changed` â filter by type

Return paginated notifications with filter state passed to Inertia.

#### Scheduler: `notifications:remind-overdue`

New console command in `app/Console/Commands/RemindOverduePayments.php`.

Registered in `routes/console.php`, runs hourly:
```
$schedule->command('notifications:remind-overdue')->hourly();
```

Logic:
1. Find all payments with `status = 'pending'` and `created_at < now() - 24 hours`
2. For each, send `payment.overdue` notification to the user
3. Skip if user already has an unread `payment.overdue` notification for the same payment

### Frontend

#### Badge Counter: `AppHeader.vue`

- Add Bell icon next to user avatar
- Poll `GET /notifications/unread-count` every 30 seconds via `setInterval` in `onMounted` / `usePoll`
- Display badge with count (red chip) when count > 0
- Click opens dropdown with 4 latest unread notifications + "Lihat semua" link to dashboard

#### Dashboard Notification Section

In `Dashboard.vue`:
- Add filter tabs row: `Semua` | `Belum dibaca` | `Tagihan` | `Pembayaran` | `Settlement`
- Each tab is an Inertia Link that reloads with `?filter=...`
- "Tandai semua dibaca" button â PATCH to `/notifications/mark-all-read`
- Each notification card shows: icon (by type), message, relative timestamp, and action link

#### New Notifications

| Trigger | Type | Message | Link |
|---------|------|---------|------|
| Member kicked | `member.removed` | "Kamu dikeluarkan dari grup {group.name}" | null |
| Member left | `member.left` | "{member.name} keluar dari grup {group.name}" | `groups.show` |
| Group status changed | `group.status_changed` | "Status grup {group.name} berubah ke {status}" | `groups.show` |
| Payment overdue (24h+) | `payment.overdue` | "Tagihan {expense.title} belum dibayar" | `payments.index` |

### Testing

- `GroupMemberTest`: kick member, leave, last-admin promotion, last-member auto-close, non-admin cannot kick, non-member cannot access
- `GroupStatusTest`: admin can change status, non-admin forbidden, cannot reopen closed group
- `NotificationTest`: unread count endpoint, mark all read, filter, overdue command
- Update `AdminTest` if needed for new routes