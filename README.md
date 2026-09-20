# Atha Decoration — Wedding Organizer

Aplikasi web monolith untuk bisnis Wedding Organizer Atha Decoration. Aplikasi menyediakan katalog publik, akun pelanggan, pengajuan booking, detail operasional acara, dan panel administrasi Filament dalam satu project Laravel.

## Demo online

- **Website publik:** [Atha Decoration](https://celebrity-other-transition-trance.trycloudflare.com)
- **Panel admin:** [Admin Atha Decoration](https://celebrity-other-transition-trance.trycloudflare.com/admin)

URL di atas menggunakan Cloudflare Quick Tunnel untuk pengujian. URL dapat berubah ketika tunnel dimulai ulang. Untuk production gunakan domain tetap dan HTTPS.

### Akun demo yang tersedia

Akun berikut sudah ada pada database demo yang digunakan oleh tunnel:

| Area | Email | Password demo | Hak akses |
| --- | --- | --- | --- |
| Admin | `admin.demo@atha.test` | `AthaDemoAdmin!2026` | Panel Filament dan seluruh fungsi admin |
| Customer | `user.demo@atha.test` | Hubungi pemilik environment demo | Katalog, profil, dan booking milik sendiri |

Password di atas hanya untuk pengujian environment demo. Jangan gunakan password tersebut untuk production, jangan gunakan ulang pada akun lain, dan ganti atau nonaktifkan akun demo sebelum URL dibagikan kepada publik.

Jika password demo perlu diatur ulang, jangan menaruh password baru di source code; jalankan melalui terminal lokal setelah database tersedia:

```bash
php artisan tinker
```

```php
$admin = App\Models\User::where('email', 'admin.demo@atha.test')->firstOrFail();
$admin->forceFill(['password' => Illuminate\Support\Facades\Hash::make('PASSWORD_ADMIN_BARU')])->save();

$customer = App\Models\User::where('email', 'user.demo@atha.test')->firstOrFail();
$customer->forceFill(['password' => Illuminate\Support\Facades\Hash::make('PASSWORD_CUSTOMER_BARU')])->save();
```

Ganti placeholder password sebelum menjalankan perintah. Akun demo wajib diganti atau dinonaktifkan sebelum aplikasi digunakan untuk pelanggan sebenarnya.

## Teknologi

- PHP 8.4 minimum; PHP 8.5 digunakan pada development saat ini.
- Laravel 13.32.
- Filament 5.8 untuk panel admin.
- Laravel Fortify untuk login, register, logout, reset password, dan konfirmasi password.
- Laravel Socialite dan provider Socialite untuk backend social authentication.
- Blade dan Livewire 4 melalui Filament.
- Tailwind CSS 4.3 dan Vite 8.3.
- Node.js 22.12+ atau Node.js 24 LTS.
- MySQL/MariaDB untuk aplikasi.
- SQLite in-memory untuk feature test.
- PHPUnit 12 dan Laravel Pint.

Versi dependency dikunci pada `composer.lock` dan `package-lock.json`.

## Fitur utama

### Website publik

- Home
- Tentang Kami
- Daftar dan detail paket wedding
- Gallery / portfolio
- Kontak
- CTA WhatsApp menggunakan `wa.me`
- Tema light/dark dengan penyimpanan preference browser
- Responsive mobile-first menggunakan Blade dan Tailwind

Hanya paket aktif dan gallery yang dipublikasikan yang ditampilkan.

### Area customer

- Register, login, logout, reset password, dan konfirmasi password
- Profil nama, email, dan nomor WhatsApp
- Melihat paket dan detail isi setiap section
- Booking dengan alur isi data → review → persetujuan terms → konfirmasi
- Snapshot nama, harga, isi paket, dan terms saat booking dibuat
- Kalender ketersediaan maksimal tiga booking per tanggal
- Daftar dan detail booking milik sendiri
- Detail wedding/event dan vendor yang sudah ditentukan admin
- Status booking: Pending, Accepted, Completed, dan Cancelled

Nomor WhatsApp wajib tersedia sebelum booking dikonfirmasi. Authorization backend mencegah customer mengakses booking customer lain.

### Panel admin

Panel berada di `/admin` dan menggunakan Filament.

- Dashboard statistik sederhana
- Kelola customer
- Kelola paket dengan section fleksibel melalui repeater
- Kelola gallery dan upload JPEG/PNG/WebP tervalidasi
- Kelola booking dan statusnya
- Kelola detail operasional event
- Kelola status pembayaran manual
- Tombol WhatsApp customer melalui `wa.me`

Akses admin ditentukan oleh `users.is_admin`. Customer biasa tidak dapat masuk panel atau resource admin walaupun mengetahui URL-nya. Tidak ada registrasi admin publik.

Backend social authentication tetap tersedia, tetapi tombol social login customer saat ini disembunyikan sampai credential provider dikonfigurasi dan diaktifkan.

## Persiapan development lokal

### Prasyarat

- PHP 8.4+ dengan ekstensi Ctype, cURL, DOM/XML, Fileinfo, Filter, Hash, Intl, Mbstring, OpenSSL, PDO MySQL, Session, Tokenizer, dan ZIP.
- Composer 2.9+.
- Node.js 22.12+ atau 24 LTS dan npm.
- MySQL/MariaDB.

Periksa runtime:

```bash
php --version
composer --version
node --version
npm --version
mariadb --version
```

### Instalasi

```bash
git clone <URL_REPOSITORY>
cd Atha_decoration-Web
composer install
cp .env.example .env
php artisan key:generate
npm ci --ignore-scripts
npm run build
```

Isi koneksi database pada `.env`, lalu jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
```

Jangan commit `.env`, credential OAuth, password database, atau private key.

### Environment minimum

```env
APP_NAME="Atha Decoration"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=atha_decoration
DB_USERNAME=atha_app
DB_PASSWORD=...

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
```

`APP_KEY` harus dipertahankan setelah dibuat. Jangan memakai akun root database untuk aplikasi.

### Menjalankan aplikasi

Untuk development dengan hot reload:

```bash
composer run dev
```

Buka `http://127.0.0.1:8000`.

Untuk menjalankan build production melalui Cloudflare Tunnel, jangan jalankan `npm run dev` karena command tersebut membuat `public/hot` dan mengarahkan browser ke Vite pada port `5173`.

```bash
rm -f public/hot
npm run build
php artisan optimize:clear
php artisan serve --host=0.0.0.0 --port=8000
```

Pada terminal lain:

```bash
cloudflared tunnel --url http://127.0.0.1:8000
```

Gunakan URL HTTPS yang diberikan Cloudflare. URL Quick Tunnel bersifat sementara. Untuk domain tetap, gunakan Cloudflare Named Tunnel dan domain yang Anda miliki.

## Social authentication

Backend mendukung Google, Apple, Microsoft, Facebook, dan X/Twitter. Semua provider default-nya nonaktif dan credential dibaca dari environment. Dokumentasi lengkap:

- [Panduan social authentication](docs/social-authentication.md)

Contoh konfigurasi Google:

```env
SOCIAL_LOGIN_UI_ENABLED=true
GOOGLE_ENABLED=true
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://domain-anda.example/auth/google/callback
```

Jangan mengaktifkan provider sebelum redirect URI yang sama persis terdaftar pada dashboard provider. Credential asli tidak boleh dimasukkan ke README atau Git.

## Struktur project

| Lokasi | Fungsi |
| --- | --- |
| `app/Actions` | Aturan aplikasi yang memiliki alur lebih dari CRUD sederhana |
| `app/Enums` | Enum status booking, pembayaran, dan provider |
| `app/Filament` | Resource dan widget panel admin |
| `app/Http/Controllers` | Controller public, customer, profil, booking, dan social auth |
| `app/Models` | Model Eloquent dan relationship |
| `app/Policies` | Authorization ownership dan admin |
| `app/Services` | Logika availability dan locking booking |
| `bootstrap`, `config` | Bootstrap Laravel dan konfigurasi aplikasi |
| `database/migrations` | Struktur database dan constraint |
| `database/seeders` | Import katalog paket idempotent |
| `resources/views` | Layout dan halaman Blade |
| `resources/css`, `resources/js` | Tailwind dan JavaScript ringan |
| `routes/web.php` | Route web berbasis session |
| `public/build` | Asset hasil build Vite, dibuat saat build |
| `tests/Feature` | PHPUnit untuk auth, booking, admin, catalog, dan security |
| `docs` | Arsitektur, roadmap, development, dan social auth |

## Testing dan quality checks

Jalankan secara berurutan agar proses build tidak menghapus manifest ketika test Filament sedang berjalan:

```bash
php artisan test --compact
npm run build
vendor/bin/pint --dirty --format agent
php artisan view:cache
php artisan route:list --except-vendor
```

Test suite saat dokumentasi ini diperbarui: **94 test dan 855 assertions**.

## Deployment checklist

Sebelum deployment:

- Gunakan document root `public/`.
- Set `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` HTTPS.
- Isi `APP_KEY` valid dan stabil.
- Konfigurasikan DB MySQL/MariaDB melalui secret manager hosting.
- Jalankan `composer install` dan `npm run build` dari lockfile.
- Jalankan `php artisan migrate --force` hanya pada database deployment yang benar.
- Siapkan SMTP untuk reset password.
- Siapkan storage persisten dan backup database/foto.
- Pastikan `public/hot` tidak ada pada deployment production.
- Jangan mengaktifkan social login tanpa credential dan redirect URI valid.

Untuk Railway, `PORT` disediakan otomatis oleh platform. Port database (`3306`) berbeda dari port HTTP aplikasi. Gunakan reference variable database Railway, bukan `127.0.0.1`.

## Di luar scope V1

Versi pertama sengaja tidak mencakup payment gateway, WhatsApp API, realtime chat, vendor marketplace, accounting, invoice kompleks, Google Calendar, multi-tenant, mobile app, SPA terpisah, microservices, atau fitur AI.

## Dokumen lanjutan

- [Arsitektur dan keputusan bisnis](docs/architecture.md)
- [Roadmap implementasi](docs/roadmap.md)
- [Panduan development](docs/development.md)
- [Setup social authentication](docs/social-authentication.md)
- [Milestone fondasi](docs/milestones/01-foundation.md)
- [Laporan fitur V1](docs/milestones/02-website-features.md)

Git commit, push, dan pengelolaan credential tetap dilakukan oleh pemilik proyek.
