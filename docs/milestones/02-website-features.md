# Laporan M2–M8 — Website dan fitur V1

## Implementasi

Milestone ini mengaktifkan autentikasi pelanggan Fortify, akses admin Filament,
katalog publik, galeri, profil, pengajuan booking, daftar/detail booking, policies,
status workflow, dan statistik dashboard. Harga booking disimpan sebagai snapshot agar
perubahan paket tidak mengubah riwayat. Paket dinonaktifkan melalui `is_active`; record
yang sudah direferensikan tidak dihapus. Upload admin dibatasi ke JPEG/PNG/WebP hingga
2 MiB dan file lama dibersihkan ketika record diganti atau dihapus.

Arsitektur tetap monolith Laravel: route/controller Blade untuk publik dan pelanggan,
action kecil untuk transisi status, Eloquent untuk persistence, dan Filament untuk CRUD
admin. Tidak ada API, SPA, permission package, atau integrasi di luar scope V1.

## File utama

Dibuat atau diubah:

- `app/Actions/Bookings/ChangeBookingStatus.php` dan action Fortify.
- `app/Enums/BookingStatus.php`.
- Model, factory, migration, dan policy untuk user, paket, booking, dan gallery.
- Controller, Form Request, `routes/web.php`, provider Fortify, config Fortify,
  `config/services.php`, dan timezone aplikasi.
- Resource/page/widget Filament di `app/Filament/`.
- Layout serta view Blade publik, auth, profil, dan booking di `resources/views/`.
- `tests/Feature/FoundationTest.php`, `BookingTest.php`, dan `ContentTest.php`.
- README dan dokumentasi development/roadmap.

## Verifikasi

- `php artisan test`: **51 test lulus, 410 assertion**.
- `vendor/bin/pint --test`: lulus.
- `php artisan view:cache`: lulus.
- `php artisan filament:cache-components`: lulus.
- `php artisan route:list --path=admin`: empat resource admin terdaftar.
- `npm run build`: lulus dengan Vite 8.
- HTTP smoke check: `/`, `/tentang-kami`, `/paket-wedding`, `/gallery`, `/kontak`,
  `/login`, dan `/register` merespons 200; guest `/admin` dialihkan ke `/admin/login`.
- Browser smoke check desktop dan viewport mobile menunjukkan navigasi, hierarchy,
  CTA, dan empty state terbaca. Data katalog belum diisi sehingga halaman menampilkan
  empty state yang sesuai.

Migrasi feature diuji pada SQLite disposable dan status seluruh migrasi diverifikasi pada
MariaDB lokal dengan `php artisan migrate:status`. Verifikasi ini melengkapi smoke test
SQLite; backup/restore dan deployment produksi tetap menjadi pekerjaan M9.

## Review kedua

Review independen memeriksa correctness, root cause, arsitektur, readability,
maintainability, SOLID/coupling, error handling, edge case, performance, security,
regression risk, compatibility, testability, dan kesesuaian dengan codebase.

- Putaran review terakhir: **4/5**.
- Skor review terakhir: **8,7/10**.
- Temuan penting dan perbaikan: provider Fortify package belum terdaftar sehingga route
  login/register hilang; diperbaiki di `bootstrap/providers.php`. Test lama yang masih
  mengharapkan 404 diperbarui menjadi test autentikasi nyata. IDOR booking diuji dengan
  ownership check, custom status action diberi authorization, status dinamis di Blade
  diganti class statis agar Tailwind menghasilkan CSS, cleanup file upload ditambah
  pada model events, stale price dicek ulang di dalam transaksi, dan link guest memakai
  intended URL native Laravel agar paket yang dipilih tidak hilang setelah login.
  Putaran koreksi terakhir menyamakan disk URL gambar dengan disk upload, memakai
  `Password::defaults()` pada form admin, menambahkan skip link pada halaman akun,
  memberi tie-breaker ID pada pagination katalog, serta menyelaraskan dokumentasi
  dengan hasil verifikasi aktual.

## Risiko tersisa

Kapasitas tanggal dan arti Accepted masih keputusan bisnis yang belum dikonfirmasi;
V1 tidak mengunci satu booking per tanggal. SMTP reset password, HTTPS, storage link,
backup/restore MariaDB dan foto, konten bisnis final, serta `WHATSAPP_NUMBER` harus
diverifikasi pada M9. File publik yang sudah diunggah bersifat public sesuai kebutuhan
katalog.
