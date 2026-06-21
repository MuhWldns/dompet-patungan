# DOKUMEN REQUIREMENTS & ARSITEKTUR TEKNIS

[cite_start]**Sistem:** Dompet Patungan (Aplikasi Manajemen Patungan & Split Tagihan) [cite: 2, 3]
**Tech Stack:** Laravel 11 (Backend & Routing) + Vue.js 3 (Frontend) + Inertia.js (Bridge) + PostgreSQL (Database)

---

## 1. Ringkasan Sistem

[cite_start]Sistem ini memfasilitasi manajemen patungan, pembagian tagihan (split expense), dan penyelesaian utang (settlement) antar anggota grup. [cite: 3]

- **Auth:** Laravel Session / Cookie-based Authentication (Stateful). Waktu kedaluwarsa diset 24 jam (`config/session.php`).
- [cite_start]**File Upload:** Cloudinary atau AWS S3 untuk bukti bayar & struk. [cite: 80]
- **Validasi:** Laravel Form Request (Backend) untuk memastikan data bersih sebelum dirender kembali oleh Inertia.

---

## 2. Functional Requirements (FR)

- [cite_start]**FR-01 (Autentikasi & Otorisasi):** Register dan login[cite: 12, 13]. Sistem memvalidasi kredensial dan membuat _session cookie_.
- [cite_start]**FR-02 (Manajemen Grup):** Admin dapat membuat grup, mengundang member via link/email, menghapus member, dan mengubah status grup (active, settled, closed). [cite: 17, 18, 19, 21, 22]
- **FR-03 (Manajemen Expense):** Admin dapat menambah pengeluaran, upload struk, dan memilih metode split (rata atau kustom per orang). [cite_start]Sistem otomatis membuat record tagihan per member. [cite: 24, 25, 26, 27]
- **FR-04 (Pembayaran Tagihan):** Member melihat tagihan, memilih metode (transfer, tunai, QRIS), dan upload bukti bayar. [cite_start]Admin mengkonfirmasi atau menolak pembayaran tersebut. [cite: 30, 31, 32, 33, 34]
- **FR-05 (Settlement & Rekap Utang):** Sistem menghitung utang bersih antar member untuk meminimalkan transaksi. [cite_start]Rekap (siapa bayar ke siapa) bisa dilihat oleh semua member. [cite: 37, 38, 39, 40, 41]
- [cite_start]**FR-06 (Notifikasi):** Sistem mengirim notifikasi untuk tagihan baru, reminder telat bayar, konfirmasi admin, dan hasil settlement. [cite: 43, 44, 45, 46, 47]
- [cite_start]**FR-07 (Manajemen Admin Sistem):** Admin sistem memiliki tampilan terpisah dari user biasa dan berfokus pada kontrol platform: manajemen akun user, aktivasi/nonaktivasi akun untuk mencabut akses, monitoring grup secara agregat, statistik operasional, dan kontrol administratif lain tanpa membuka detail pengeluaran/privasi user. [cite: 49, 50, 51, 52, 53, 54]

---

## 3. Non-Functional Requirements (NFR)

- [cite_start]**Keamanan:** Password di-hash menggunakan bcrypt (salt rounds >= 10). [cite: 57] Perlindungan CSRF ditangani otomatis oleh Laravel. Admin Sistem diproteksi menggunakan Middleware Role. [cite_start]Komunikasi wajib HTTPS. [cite: 57]
- [cite_start]**Performa:** Load time halaman utama < 2 detik. [cite: 57] [cite_start]Database dioptimalkan dengan indexing pada FK dan kolom filter. [cite: 57]
- [cite_start]**Usability:** UI responsif (Mobile First). [cite: 57] [cite_start]Alur bayar tagihan tidak lebih dari 3 langkah. [cite: 57] UX terasa seperti SPA walau menggunakan routing backend.
- [cite_start]**Skalabilitas & Ketersediaan:** Database connection pooling (maks 10 koneksi). [cite: 57] [cite_start]Target uptime 99%. [cite: 57]
- [cite_start]**Maintainability:** Validasi input terpusat di Form Request. [cite: 57] Kode mengikuti pola MVC standar Laravel yang diinjeksikan ke komponen Vue.

---

## 4. Struktur Database (Laravel Migrations)

Pemetaan tabel tetap sama, namun tanpa keperluan tabel/kolom tambahan untuk manajemen token:

- [cite_start]**`users`**: `id` (uuid), `name`, `email`, `password` (bcrypt), `role` (user, system_admin). [cite: 74]
- [cite_start]**`groups`**: `id` (uuid), `name`, `description`, `target_amount`, `status` (active, settled, closed). [cite: 74]
- [cite_start]**`group_members`**: `group_id`, `user_id`, `role` (admin, member). [cite: 74]
- **`expenses`**: `id` (uuid), `group_id`, `title`, `amount`, `category`, `date`, `receipt_url`, `status` (pending, locked). [cite: 74]
- [cite_start]**`payments`**: `id` (uuid), `expense_id`, `user_id`, `amount`, `payment_method`, `proof_url`, `status` (pending, confirmed, rejected). [cite: 74]
- [cite_start]**`settlements`**: `id` (uuid), `group_id`, `debt_details` (jsonb). [cite: 74]
- **`notifications`**: `id` (uuid), `user_id`, `message`, `is_read`. [cite: 74]

---

## 5. Arsitektur Backend & Routing (Laravel `web.php`)

Routing tidak lagi menggunakan prefix `/api`. Semua _route_ didaftarkan di `routes/web.php` dan _controller_ mengembalikan response menggunakan `Inertia::render()`.

**Auth & Guest**

- `GET /login`, `POST /login`
- `GET /register`, `POST /register`
- `POST /logout`

**Member (Middleware: `auth`)**

- `GET /groups` - Tampilan daftar grup.
- `POST /groups/{id}/join` - Proses join via link.
- `GET /payments` - Tampilan tagihan user.
- `POST /payments/{id}/pay` - Proses form bukti bayar.
- `GET /settlements/{groupId}` - Tampilan hasil akhir.

**Admin Grup (Middleware: `auth`, Custom Middleware: `group.admin`)**

- `POST /groups` - Simpan grup baru.
- `POST /groups/{id}/invite` - Proses undang member.
- `POST /groups/{id}/expenses` - Simpan form pengeluaran & jalankan _split logic_.
- `PATCH /payments/{id}/confirm` - Proses konfirmasi pembayaran.
- `POST /settlements/generate/{groupId}` - Proses hitung settlement.

**Admin Sistem (Middleware: `auth`, Custom Middleware: `role:system_admin`)**

- `GET /admin/users` - Tampilan user control center untuk melihat akun, role, status aktif/nonaktif, dan menjalankan deactivate/reactivate account.
- `PATCH /admin/users/{user}/status` - Kontrol aktif/nonaktif akun user. System admin tidak boleh menonaktifkan akunnya sendiri.
- `GET /admin/groups` - Tampilan group monitoring untuk melihat agregat kesehatan grup, owner, jumlah member, jumlah pengeluaran, dan settlement tanpa membuka detail privat transaksi.
- `GET /admin/stats` - Tampilan monitoring center berisi metrik agregat platform: user access, group health, payment operations, dan total nominal agregat.

Catatan otorisasi: `system_admin` bukan admin semua grup. Role ini mengelola platform secara agregat dan kontrol akses user. Aksi operasional grup seperti tambah pengeluaran, konfirmasi pembayaran, dan generate settlement tetap membutuhkan role `admin` pada `group_members` untuk grup terkait.

---

## 6. Arsitektur Frontend (Vue 3 + Inertia)

### A. Konsep Alur Data

- **Routing:** Vue Router ditiadakan. Navigasi menggunakan komponen `<Link>` dari `@inertiajs/vue3`.
- **Data Fetching:** Axios ditiadakan untuk load halaman. Data tagihan atau grup dikirim dari Laravel Controller langsung sebagai _Props_ ke komponen halaman Vue.
- **Shared Data:** State global seperti informasi `user` yang sedang login atau pesan notifikasi _flash_ (berhasil/gagal) disuntikkan secara otomatis ke semua komponen Vue melalui file `HandleInertiaRequests.php` di Laravel.
- **Role-based UI:** User biasa melihat dashboard, grup, pembayaran, settlement, dan notifikasi personal. `system_admin` diarahkan ke area `/admin/*` dengan navigasi berbeda yang hanya berisi user control, group monitoring, dan platform metrics.

### B. Folder Structure

- `resources/js/Pages/` - Berisi komponen utama yang merender satu URL penuh (contoh: `Auth/Login.vue`, `Groups/Index.vue`, `Payments/Show.vue`).
- `resources/js/Components/` - Berisi potongan UI yang _reusable_ (contoh: `PrimaryButton.vue`, `ExpenseCard.vue`, `ModalUpload.vue`).
- `resources/js/Layouts/` - Berisi _wrapper_ layout (contoh: `AuthenticatedLayout.vue` yang membawa Navbar dan Sidebar).

### C. Tampilan Admin Sistem

- Sidebar/header untuk `system_admin` berbeda dari user biasa: menu utama diarahkan ke `/admin/stats`, `/admin/users`, dan `/admin/groups`.
- `/dashboard` untuk `system_admin` melakukan redirect ke `/admin/stats` agar admin langsung masuk ke monitoring center.
- `/admin/users` menampilkan ringkasan total/aktif/nonaktif/system admin dan tombol deactivate/reactivate account.
- `/admin/groups` menampilkan monitoring grup agregat: status grup, owner, jumlah member, jumlah pengeluaran, dan jumlah settlement.
- `/admin/stats` menampilkan monitoring operasional agregat dan tidak menampilkan judul/detail pengeluaran privat.
