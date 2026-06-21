# Dompet Patungan

Dompet Patungan adalah aplikasi web untuk mengelola patungan, split tagihan, pembayaran, dan settlement antar anggota grup. Aplikasi ini menggunakan Laravel sebagai backend dan routing utama, Vue 3 sebagai frontend, serta Inertia.js sebagai penghubung agar pengalaman pengguna tetap terasa seperti SPA tanpa API terpisah.

## Fitur Utama

- Autentikasi user dengan Laravel Fortify.
- Manajemen grup patungan.
- Invite link untuk join grup.
- Pencatatan pengeluaran grup.
- Split tagihan per anggota.
- Pembayaran tagihan oleh member.
- Konfirmasi atau penolakan pembayaran oleh admin grup.
- Settlement untuk menghitung rekap utang bersih.
- Notifikasi aplikasi.
- Manajemen user dan statistik untuk admin sistem.

## Tech Stack

- Laravel 13
- PHP 8.3+
- Vue 3
- Inertia.js v3
- Tailwind CSS v4
- Laravel Fortify
- Laravel Wayfinder
- Pest 4
- Larastan / PHPStan
- MySQL
- Vite

## Kebutuhan Lokal

Pastikan environment lokal memiliki:

- PHP 8.3 atau lebih baru
- Composer
- Node.js 22 atau lebih baru
- npm
- MySQL

## Setup Lokal

Clone repository:

```bash
git clone https://github.com/MuhWldns/dompet-patungan.git
cd dompet-patungan
```

Install dependency PHP:

```bash
composer install
```

Buat file environment:

```bash
cp .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dompet_patungan
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi:

```bash
php artisan migrate
```

Install dependency frontend:

```bash
npm install
```

Jalankan development server:

```bash
composer dev
```

Command tersebut menjalankan Laravel server, queue listener, dan Vite dev server secara bersamaan.

## Build Production

Untuk build asset frontend:

```bash
npm run build
```

Untuk production Laravel:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Command Development

Menjalankan app lokal:

```bash
composer dev
```

Format PHP:

```bash
composer lint
```

Cek format PHP:

```bash
composer lint:check
```

Format frontend:

```bash
npm run format
```

Cek format frontend:

```bash
npm run format:check
```

Lint frontend:

```bash
npm run lint
```

Cek lint frontend:

```bash
npm run lint:check
```

Cek type frontend:

```bash
npm run types:check
```

Cek type backend:

```bash
composer types:check
```

Jalankan test:

```bash
php artisan test
```

Jalankan semua check utama:

```bash
composer ci:check
```

## Struktur Penting

- `routes/web.php`: route utama aplikasi web/Inertia.
- `routes/settings.php`: route halaman settings.
- `app/Http/Controllers`: controller Laravel.
- `app/Models`: model domain aplikasi.
- `resources/js/pages`: halaman Vue yang dirender oleh Inertia.
- `resources/js/components`: komponen Vue reusable.
- `resources/js/layouts`: layout aplikasi.
- `database/migrations`: struktur database.
- `tests/Feature`: test fitur dengan Pest.
- `Docs`: dokumentasi requirement dan desain.

## Alur Aplikasi

Aplikasi menggunakan Laravel web routes, bukan API terpisah. Flow utamanya:

```text
Browser -> Laravel Route -> Controller -> Inertia::render -> Vue Page
```

Frontend melakukan navigasi menggunakan Inertia `<Link>` dan form Inertia. Data halaman dikirim dari controller sebagai props.

## Deployment VPS Ringkas

Komponen minimal di VPS:

- Nginx
- PHP 8.3 FPM
- MySQL
- Composer
- Node.js
- Supervisor untuk queue worker
- Certbot untuk HTTPS

Flow deploy manual:

```bash
cd /var/www/dompet-patungan
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
```

Untuk production, pastikan `.env` menggunakan:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com
QUEUE_CONNECTION=database
```

Queue worker dapat dijalankan dengan Supervisor:

```bash
php artisan queue:work database --sleep=3 --tries=3 --max-time=3600
```

## Catatan Production

- Jangan commit file `.env`.
- Gunakan HTTPS.
- Pastikan `APP_DEBUG=false`.
- Pastikan folder `storage` dan `bootstrap/cache` writable oleh web server.
- Gunakan database user khusus, bukan root.
- Jalankan queue worker jika fitur notifikasi/email/job background digunakan.
- Siapkan SMTP production jika fitur email seperti reset password digunakan.
- Siapkan backup database berkala.

## License

Project ini menggunakan lisensi MIT.
