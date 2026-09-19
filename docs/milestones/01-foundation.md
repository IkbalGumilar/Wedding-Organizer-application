# M1 — Fondasi proyek

Selesai pada 16 September 2026. Tujuan: aplikasi Laravel dapat dijalankan dan diuji
secara lokal, dengan dependensi yang kompatibel serta panduan pengembangan.

## Implementasi

- Scaffold resmi `laravel/laravel` v13.10.1, commit `5aad4ddf34d5e21dfe6b4c07eeac67d5bd5e08b0`.
- Dependency lock: Laravel 13.32.0, Filament 5.8.2, Livewire 4.4.5, Fortify 1.39.0.
- Frontend: Vite 8.3.0, plugin Laravel 3.2.0, Tailwind 4.3.3.
- Runtime akhir yang diuji: PHP 8.5.4, Composer 2.9.5, Node 24.21.0, npm 11.19.0.
- MariaDB 11.8.6; akun aplikasi terpisah, kredensial acak lokal, migrasi standar selesai.
- Session/cache file, queue sync, email log untuk development.
- Composer scripts untuk dua server lokal dan pemeriksaan; lockfile untuk instalasi ulang.
- Halaman sementara Atha Decoration, tanpa marketing framework atau font eksternal.
- Dokumentasi arsitektur, keputusan bisnis tertunda, roadmap, dan setup lokal.

Fortify/passkey discovery dinonaktifkan sementara dan panel Filament belum dibuat.
Ini mencegah endpoint autentikasi yang belum dikonfigurasi menjadi bagian aplikasi.
Seeder tidak membuat akun contoh. Tidak ada model/migrasi booking, resource admin,
atau abstraksi domain yang belum dipakai.

Node dipasang dari arsip resmi yang SHA-256-nya diverifikasi ke direktori pengguna,
dengan symlink di `~/.local/bin`. PHP, Composer, dan MariaDB dipasang pengguna melalui
APT dan diperiksa kembali. Runtime PHP/Composer sementara hanya dipakai saat bootstrap;
verifikasi akhir menggunakan runtime Ubuntu yang terpasang.

## Verifikasi

| Pemeriksaan | Hasil |
| --- | --- |
| `composer validate --strict` | Lulus; manifest dan lockfile sesuai |
| `composer check-platform-reqs` | Lulus pada PHP sistem 8.5.4 |
| `vendor/bin/pint --test` | Lulus |
| `php artisan test` | 2 tes, 10 assertions lulus |
| Tes dengan APP_NAME/APP_LOCALE/DB shell yang berbeda | Tetap lulus; isolasi konfigurasi bekerja |
| `npm run build` | Lulus; manifest dan aset Vite dihasilkan |
| Audit saat resolusi Composer / instalasi npm | Tidak ada advisory yang dilaporkan saat pemeriksaan |
| Koneksi database lokal dan `migrate:status` | Lulus; tiga migrasi bawaan sudah dijalankan |
| Migrasi, rollback, migrasi ulang pada MariaDB disposable | Lulus; database operasional tidak di-rollback |
| `composer run dev` | Laravel dan Vite berjalan pada loopback |
| HTTP `/` dan `/up` | 200 |
| HTTP `/admin`, `/login`, `/passkeys/login/options` | 404 sesuai scope fondasi |
| Aset CSS Vite melalui HTTP | 200 |

Server development dan MariaDB disposable dihentikan setelah pemeriksaan. Database
lokal utama tetap tersedia. File provisioning sekali pakai telah dihapus. Password
yang sempat ikut tercetak pada error SQL diganti, `.env` diperbarui, dan koneksi baru
diverifikasi tanpa mencetak kredensial.

## Review

Review pertama menemukan ketergantungan tes pada `.env` lokal. Tes sekarang mengatur
nama/locale sendiri dan PHPUnit mengunci database SQLite in-memory serta APP_ENV
testing. Pemeriksaan route juga menemukan auto-discovery passkey dari dependency
Fortify; discovery tersebut dinonaktifkan sampai fitur yang relevan disiapkan.

Metadata lockfile diselaraskan setelah konfigurasi discovery berubah. Formatting
seeder diperbaiki dengan Pint. Reviewer terpisah memeriksa kembali source dan panduan
development setelah perubahan; tidak ada temuan material tersisa untuk scope M1.

**Skor akhir: 9/10. Putaran review: 2/5.**

Batas hasil: ini fondasi yang layak dilanjutkan, bukan aplikasi siap produksi.
Autentikasi pelanggan, admin authorization, booking, upload, dan deployment harus
diimplementasikan dan diuji pada milestone masing-masing. Empat keputusan bisnis
dalam dokumen arsitektur belum ditetapkan.

## File dibuat dan diubah

Workspace sebelumnya belum memiliki source aplikasi. Seluruh source berikut dibuat
pada M1; perubahan yang disebutkan adalah penyesuaian terhadap scaffold baru.

- Dibuat: struktur standar Laravel pada `app/`, `bootstrap/`, `config/`, `database/`,
  `public/`, `resources/`, `routes/`, `storage/`, `tests/`, beserta `artisan`.
- Dibuat: `composer.lock`, `package-lock.json`, `.nvmrc`, dan seluruh dokumen proyek.
- Disesuaikan: `composer.json`, `package.json`, `.env.example`, `.gitignore`,
  `config/app.php`, `vite.config.js`, `phpunit.xml`, `routes/web.php`,
  `resources/css/app.css`, `resources/views/welcome.blade.php`,
  `database/seeders/DatabaseSeeder.php`, dan `README.md`.
- Tes contoh scaffold diganti dengan `tests/Feature/FoundationTest.php`;
  direktori `tests/Unit/` disimpan untuk tes domain berikutnya.
- Lokal/diabaikan Git: `.env`, `vendor/`, `node_modules/`, hasil build, cache, dan log.

Tidak ada commit, amend, push, tag, atau perubahan history Git. Direktori `.git`
yang disediakan workspace tidak dikenali sebagai repository aktif pada pemeriksaan;
tidak ada metadata repository yang ditimpa.
