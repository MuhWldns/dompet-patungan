# Source Code Map — Group Member Management & Notifications

> Lokasi kode untuk semua fitur yang diimplementasi. Update: 2026-06-23.

## Feature 1: Member Removal (Kick & Leave)

### Backend

| Komponen | File | Deskripsi |
|----------|------|-----------|
| Controller | `app/Http/Controllers/GroupMemberController.php` | Handler `destroy()` — kick admin, self-leave, auto promote admin, auto close group |
| Route | `routes/web.php:26` | `DELETE /groups/{group}/members/{user}` → `groups.members.destroy` |
| Test | `tests/Feature/GroupMemberTest.php` | 8 tests: kick, leave, permission guard, admin promotion, group closure, notifikasi kick, notifikasi leave |

### Frontend

| Komponen | File | Lokasi |
|----------|------|--------|
| Tombol kick/leave | `resources/js/pages/groups/Show.vue` | Line member list — tombol ✕ dengan dialog konfirmasi |

---

## Feature 2: Group Status Changes

### Backend

| Komponen | File | Deskripsi |
|----------|------|-----------|
| Controller | `app/Http/Controllers/GroupController.php:93` | Method `updateStatus()` — admin-only, validasi settled/closed, guard group tertutup, broadcast notifikasi |
| Route | `routes/web.php:27` | `PATCH /groups/{group}/status` → `groups.status.update` |
| Test | `tests/Feature/GroupStatusTest.php` | 6 tests: settled, closed, non-admin guard, cannot reopen, notifikasi, invalid status |

### Frontend

| Komponen | File | Lokasi |
|----------|------|--------|
| Dropdown status | `resources/js/pages/groups/Show.vue` | Hero section — `<select>` Active/Settled/Closed + `updateStatus()` function |

---

## Feature 3: Enhanced Notifications

### Backend

| Komponen | File | Deskripsi |
|----------|------|-----------|
| Controller | `app/Http/Controllers/NotificationController.php` | `index()` filter + paginate, `unreadCount()` JSON, `markAllRead()`, `read()` |
| Routes | `routes/web.php:36-39` | `GET /notifications`, `GET /notifications/unread-count`, `PATCH /notifications/mark-all-read`, `PATCH /notifications/{id}/read` |
| Test | `tests/Feature/NotificationFilterTest.php` | 4 tests: unread count, mark all read, filter unread, filter by type |

### Frontend

| Komponen | File | Deskripsi |
|----------|------|-----------|
| Bell icon + badge | `resources/js/components/AppHeader.vue` | Icon lonceng dengan counter notifikasi, polling 30 detik ke `/notifications/unread-count` |
| Filter tabs | `resources/js/pages/Dashboard.vue` | Chip filter: Semua, Belum dibaca, Tagihan, Pembayaran, Settlement. + tombol "Tandai semua dibaca" |
| Notification list | `resources/js/components/NotificationList.vue` | Support array biasa & paginated `{ data: [...] }`, efek opacity untuk notif sudah dibaca |

### Dashboard Backend

| Komponen | File | Deskripsi |
|----------|------|-----------|
| Controller | `app/Http/Controllers/DashboardController.php` | Support query `?filter=unread` / `?filter=bill.created` dsb., pass `filter` prop ke Vue |

---

## Feature 4: Overdue Payment Reminders

| Komponen | File | Deskripsi |
|----------|------|-----------|
| Artisan Command | `app/Console/Commands/RemindOverduePayments.php` | Cari payment pending >24 jam, kirim notif `payment.overdue` dengan deduplikasi |
| Schedule | `routes/console.php:8` | `Schedule::command('notifications:remind-overdue')->hourly()` |

---

## Clave: Ringkasan File

```
app/
├── Console/Commands/RemindOverduePayments.php   ← NEW
├── Http/Controllers/
│   ├── DashboardController.php                    ← MODIFIED (filter support)
│   ├── GroupController.php                        ← MODIFIED (updateStatus)
│   ├── GroupMemberController.php                  ← NEW
│   └── NotificationController.php                 ← MODIFIED (index, unreadCount, markAllRead)

resources/js/
├── components/
│   ├── AppHeader.vue                              ← MODIFIED (bell + badge + polling)
│   └── NotificationList.vue                       ← MODIFIED (paginated support, opacity)
├── pages/
│   ├── Dashboard.vue                              ← MODIFIED (filter tabs, mark-all-read)
│   └── groups/Show.vue                            ← MODIFIED (remove button, status dropdown)

routes/
├── console.php                                    ← MODIFIED (scheduler)
└── web.php                                        ← MODIFIED (4 new routes)

tests/Feature/
├── GroupMemberTest.php                            ← NEW
├── GroupStatusTest.php                            ← NEW
└── NotificationFilterTest.php                     ← NEW
```
