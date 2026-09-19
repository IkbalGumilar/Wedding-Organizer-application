# Atha Decoration

Website Wedding Organizer berbasis satu aplikasi Laravel. Fitur website V1 sudah
tersedia untuk diuji secara lokal; kesiapan produksi tetap membutuhkan konfigurasi
hosting, SMTP, backup, dan konten bisnis final.

## Stack

- PHP 8.5 untuk pengembangan, minimum proyek PHP 8.4.
- Laravel 13, Filament 5, Livewire 4, dan Fortify.
- Blade, Tailwind CSS 4, Vite 8, Node 24 LTS.
- MariaDB/MySQL; session dan cache file, queue sinkron.
- PHPUnit dan Laravel Pint.

Versi dependensi tepat dikunci dalam `composer.lock` dan `package-lock.json`.
Fortify menyediakan autentikasi pelanggan dan Filament menyediakan panel admin pada
`/admin`. Akses panel dibatasi oleh flag `users.is_admin`; registrasi publik tidak
pernah dapat membuat akun admin.

## Mulai mengembangkan

Ikuti [panduan pengembangan](docs/development.md) untuk instalasi runtime,
konfigurasi environment, database, dan setup checkout baru. Setelah setup selesai:

```bash
composer run dev
```

Akses `http://127.0.0.1:8000`. Server Laravel dan Vite hanya mendengarkan loopback.
Website menyediakan katalog publik, galeri, WhatsApp CTA, akun pelanggan, pengajuan
booking, profil, serta dashboard Filament.

Pemeriksaan rutin:

```bash
composer check
npm run build
```

Konfigurasi login Google, Apple, Microsoft, Facebook, dan X/Twitter dijelaskan di
[panduan social authentication](docs/social-authentication.md). Credential provider
diisi hanya pada `.env` lokal atau secret manager deployment; `.env.example` hanya
berisi nama variabel dan nilai kosong.

## Struktur

| Lokasi | Fungsi |
| --- | --- |
| `app/` | Model, controller, provider; action/policy ditambahkan ketika fiturnya dibuat |
| `bootstrap/`, `config/` | Bootstrap dan konfigurasi standar Laravel |
| `database/` | Migrasi, factory, dan seeder |
| `resources/` | Blade, CSS, JavaScript |
| `routes/web.php` | Route web berbasis session |
| `public/` | Satu-satunya document root web server |
| `storage/` | Log, cache, dan file aplikasi |
| `tests/` | PHPUnit; tes fondasi memakai SQLite in-memory |
| `docs/` | Keputusan arsitektur, roadmap, dan panduan pengembangan |

## Dokumen

- [Arsitektur dan keputusan bisnis yang masih dibutuhkan](docs/architecture.md).
- [Roadmap dan aturan review tiap milestone](docs/roadmap.md).
- [Pengembangan lokal dan batas kesiapan produksi](docs/development.md).
- [Setup social authentication](docs/social-authentication.md).
- [Hasil pemeriksaan fondasi M1](docs/milestones/01-foundation.md).
- [Hasil implementasi fitur V1 dan review kedua](docs/milestones/02-website-features.md).

## Checklist sebelum upload ke GitHub

Pastikan `.env`, password, private key OAuth, file provisioning database, upload
pengguna di `storage/app/private` atau `storage/app/public`, `vendor/`,
`node_modules/`, cache, dan hasil build tidak ikut di-stage. Semua item tersebut
sudah dilindungi oleh `.gitignore` atau placeholder `.gitignore` di folder runtime.

Jalankan pemeriksaan berikut dari root project sebelum membuat commit:

```bash
git status --short
git diff --check
git add -n .
```

Pastikan `.env` tidak muncul pada daftar `git add -n`. Upload `composer.lock` dan
`package-lock.json` agar versi dependency dapat direproduksi; dependency terpasang
akan dibuat ulang dengan `composer install` dan `npm ci` pada checkout baru.

Commit dan operasi Git dilakukan pemilik proyek. Fitur dikerjakan satu milestone
pada satu waktu sesuai roadmap.
# Wedding-Organizer-application
