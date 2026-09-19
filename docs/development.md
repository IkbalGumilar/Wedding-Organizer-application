# Pengembangan lokal

## Runtime

- PHP 8.5 direkomendasikan; dependency resolution dibatasi pada PHP 8.4 agar lockfile juga mendukung versi minimum proyek.
- Composer 2.9 atau lebih baru.
- Node 24 LTS, versi yang diperiksa ada di `.nvmrc`; Node 22.12+ juga memenuhi batas package.
- MariaDB 11.8 untuk pengembangan ini. Gunakan engine yang sama dengan hosting untuk tes integritas domain.

Ekstensi PHP: Ctype, cURL, DOM/XML, Fileinfo, Filter, Hash, Intl, Mbstring,
OpenSSL, PDO MySQL, Session, Tokenizer, ZIP. PDO SQLite dipakai untuk tes fondasi;
GD disiapkan untuk pengolahan gambar. Periksa kebutuhan aktual dengan
`composer check-platform-reqs`.

Pada Ubuntu 26.04, jika paket belum terpasang:

```bash
sudo apt update
sudo apt install php8.5-cli php8.5-curl php8.5-mbstring php8.5-xml \
  php8.5-intl php8.5-mysql php8.5-sqlite3 php8.5-zip php8.5-gd \
  composer mariadb-server
```

Node di mesin pengembangan saat ini dipasang dari arsip resmi terverifikasi ke
`~/.local/share/nodejs/node-v24.21.0-linux-x64`; executable ditautkan ke `~/.local/bin`.
Tidak ada perubahan shell profile. Pastikan `~/.local/bin` berada dalam PATH.
Pada mesin lain gunakan instalasi Node LTS yang sudah tersedia, atau `nvm install`
jika memang telah memakai nvm. Jangan memasang runtime kedua tanpa kebutuhan.

```bash
php --version
composer --version
node --version
npm --version
mariadb --version
```

## Persiapan checkout baru

```bash
composer install
test -f .env || cp .env.example .env
chmod 600 .env
npm ci --ignore-scripts
npm run build
```

Jika `APP_KEY` di `.env` masih kosong, jalankan `php artisan key:generate` sekali.
Pertahankan key yang sudah ada. Jangan membagikan `.env`, password, atau file SQL
provisioning; semuanya merupakan data lokal dan tidak masuk Git.

## Database lokal

Gunakan akun aplikasi tersendiri dengan akses hanya pada database pengembangan.
`.env.example` menetapkan koneksi MySQL, host `127.0.0.1`, database
`atha_decoration`, dan username `atha_app`. MariaDB mendukung driver `mysql` ini.

Pada workspace pertama ini, database dan akun sudah dibuat, koneksi diverifikasi,
dan migrasi standar Laravel sudah dijalankan. Password acak tersimpan pada `.env`.
File provisioning sekali pakai telah dihapus setelah setup berhasil.

Untuk developer lain, buat database dan akun dengan nama di atas melalui admin
MariaDB, lalu isi password masing-masing pada `.env`. Contoh SQL untuk database baru:

```sql
CREATE USER 'atha_app'@'127.0.0.1' IDENTIFIED BY '<your-own-random-password>';
CREATE DATABASE atha_decoration CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON atha_decoration.* TO 'atha_app'@'127.0.0.1';
```

Jika akun/database sudah ada, periksa koneksi dan konfigurasi sebelum mengubahnya.
Jangan menjalankan ulang SQL dengan `--force` atau menimpa akun yang sudah dipakai.
Setelah kredensial `.env` benar:

```bash
php artisan migrate
php artisan migrate:status
```

Jangan menggunakan akun root untuk aplikasi. Seeder bawaan tidak membuat akun
contoh. `migrate:fresh` dan rollback hanya boleh dijalankan pada database disposable
yang memang boleh dihapus; bukan database operasional atau data pelanggan.

## Menjalankan aplikasi

```bash
composer run dev
```

Buka `http://127.0.0.1:8000`. Command menjalankan server Laravel dan Vite pada loopback;
Ctrl+C menghentikan keduanya. Alternatif setelah build: `php artisan serve --host=127.0.0.1`.
Jangan memakai development server untuk hosting produksi.

Session/cache memakai file dan queue memakai `sync`; tidak memerlukan Redis atau
worker. Migrasi infrastruktur bawaan Laravel tetap tersedia tanpa mewajibkan driver
database tersebut. Email lokal memakai `log`, sehingga tidak mengirim email sungguhan.

## Pemeriksaan

```bash
composer check
npm run build
php artisan route:list
```

`composer check` menjalankan validasi manifest/lockfile, platform requirements,
Pint, dan feature tests. Jalankan `vendor/bin/pint` bila ingin memperbaiki formatting.
Untuk tes tertentu: `php artisan test --filter=FoundationTest`.

PHPUnit fondasi secara eksplisit memakai SQLite in-memory agar variabel database
dari shell tidak mengarahkannya ke database pengembangan. Ini bukan bukti integritas
MySQL/MariaDB. Pada milestone domain, siapkan konfigurasi integration test tersendiri
dengan database disposable pada engine produksi. Migrasi MariaDB diperiksa terpisah.

## Akses admin pertama

Registrasikan akun pelanggan melalui `/register`, lalu promosikan akun tersebut sekali
melalui terminal yang memiliki akses aplikasi:

```bash
php artisan tinker
>>> $user = App\Models\User::where('email', 'admin@example.com')->firstOrFail();
>>> $user->forceFill(['is_admin' => true])->save();
>>> exit
```

Setelah itu buka `/admin`. Resource pelanggan tidak menampilkan akun admin sehingga flag
admin tidak dapat diubah dari form pelanggan. Ganti email contoh dengan alamat admin
yang benar; jangan memasukkan password atau rahasia ke dalam source code.

Passkey tetap tidak diaktifkan untuk V1. Endpoint autentikasi yang aktif adalah login,
registrasi, logout, dan reset password Fortify. Reset password memerlukan SMTP yang
benar sebelum rilis produksi.

## Status implementasi V1

Website publik, profil pelanggan, booking dengan snapshot harga, policies ownership,
CRUD paket/gallery/pelanggan, perubahan status booking, upload gambar tervalidasi, dan
statistik dashboard sudah diimplementasikan. Lihat [roadmap](roadmap.md) dan laporan
[fitur V1](milestones/02-website-features.md). Halaman About dan Contact masih berupa
konten Blade; form kontak tidak menyimpan data pada V1.

## Sebelum produksi

Gunakan document root `public/`, HTTPS, `APP_ENV=production`, `APP_DEBUG=false`,
secure cookies, akun database terbatas, dan konfigurasi SMTP yang sudah diuji.
Build aset dengan `npm ci --ignore-scripts` lalu `npm run build`; server hosting
tidak memerlukan proses Node permanen. Jalankan dependency install dari lockfile.

Pertahankan `APP_KEY`, backup database dan foto, serta periksa restore. Atur
permission storage tanpa `chmod 777`. Hapus placeholder/noindex saat website final
siap. Deployment lengkap dan panduan operasional diselesaikan pada M9, bukan M1.
