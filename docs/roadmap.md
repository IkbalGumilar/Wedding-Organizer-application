# Roadmap implementasi

Kerjakan satu milestone setiap tahap. Status di bawah diperbarui berdasarkan bukti
implementasi dan verifikasi; M9 tetap menjadi pekerjaan kesiapan produksi.
Rincian keputusan domain ada di [arsitektur](architecture.md).

## Status dan kriteria selesai

| Milestone | Tujuan dan hasil yang dapat diuji | Kriteria selesai | Status |
| --- | --- | --- | --- |
| M1 — Fondasi | Scaffold Laravel, struktur standar, tooling, dependency dan panduan development | Runtime kompatibel; aplikasi boot; koneksi DB diverifikasi; tes awal, Pint, validasi Composer, dan build berhasil | Selesai — review 9/10, putaran 2/5; lihat laporan M1 |
| M2 — Identitas dan akses | Fortify, form Blade, profil, login Filament, admin flag | Registrasi tidak dapat menaikkan hak akses; pelanggan ditolak panel; login/logout dan reset password teruji | Selesai — laporan fitur V1 |
| M3 — Integritas domain | Migrasi, model, enum, relasi, policies | Migrasi SQLite smoke test; foreign key, status, snapshot, dan ownership teruji | Selesai — laporan fitur V1 |
| M4 — Katalog admin | Paket, galeri, publikasi dan upload | Resource Filament, deaktivasi, validasi file, replacement cleanup | Selesai — laporan fitur V1 |
| M5 — Website publik | Home, About, paket, galeri, Contact, WhatsApp | Katalog tanpa login; hanya konten aktif; tampilan desktop/mobile dan build diperiksa | Selesai — laporan fitur V1 |
| M6 — Pengajuan pelanggan | Form booking, snapshot, daftar dan detail milik sendiri | Creation, IDOR, input tidak valid, paket nonaktif, dan perubahan harga teruji | Selesai — laporan fitur V1 |
| M7 — Operasional admin | Pelanggan, booking, perubahan status dan dashboard | Admin authorization, transisi ilegal, metrik, dan locking transaksi tersedia | Selesai — laporan fitur V1 |
| M8 — Verifikasi V1 | Alur lengkap, keamanan, UX mobile dan error state | Test, Pint, build, route inspection, dan browser smoke check lulus | Selesai — skor akhir 8,7/10, putaran 4/5 |
| M9 — Kesiapan produksi | Hosting, deployment, backup dan panduan admin | HTTPS, email, upload, autentikasi, booking, backup dan restore diverifikasi | Belum diimplementasikan |

Dependensi utama berurutan M1 → M2 → M3 → M4 → M5 → M6 → M7 → M8 → M9.
M3 juga membutuhkan keputusan kapasitas, arti Accepted, makna harga, dan data acara wajib.
M9 membutuhkan hosting, domain, email pengiriman, serta konten bisnis yang siap.

[Laporan M1 dan daftar file](milestones/01-foundation.md) serta [laporan fitur V1](milestones/02-website-features.md).

## Pemeriksaan tiap milestone

Pilih pemeriksaan sesuai perubahan: targeted feature/unit tests, `php artisan test`,
Laravel Pint, `npm run build`, route inspection, dan migration checks. Gunakan testing
stack yang sudah tersedia; jangan menambah dependency QA hanya untuk memenuhi daftar.
Tes ownership, privilege escalation, snapshot dan transisi lebih penting daripada
menguji ulang perilaku framework yang sederhana.

SQLite boleh untuk smoke test fondasi, tetapi bukan bukti perilaku MySQL/MariaDB.
Constraint dan konkurensi harus diuji pada engine yang digunakan aplikasi.
Laporkan pemeriksaan yang belum dapat dijalankan dan penyebabnya; jangan menyebutnya lulus.

## Review dan laporan

Setelah implementasi tiap milestone, lakukan review correctness, security, maintainability,
konvensi Laravel, integritas database, authorization, kompleksitas, UX, kemungkinan bug,
dan kepatuhan scope. Lakukan review kedua oleh reviewer independen, termasuk root cause,
readability, SOLID, coupling, error handling, edge cases, performance, regression risk,
compatibility, testability, dan kesesuaian codebase. Maksimal lima putaran koreksi/review.
Target minimal 8/10; berhenti lebih awal jika tercapai dan jangan melakukan perubahan
kosmetik untuk menaikkan skor. Jika tetap di bawah 8 setelah lima putaran, laporkan
hambatan dan risiko yang tersisa secara terbuka.

Laporan milestone menyertakan hasil, file dibuat, file diubah, keputusan teknis penting,
tes/check dan hasilnya, temuan review, perbaikan, risiko tersisa, skor akhir, dan putaran X/5.
Perbarui status berdasarkan bukti, bukan sekadar adanya file atau dependency.

Semua identifier kode memakai English. Jangan membuat placeholder class atau layer
yang belum digunakan. Scope tambahan harus ditandai Version 1, Nice to Have, atau
Future Version sebelum dikerjakan. Git commit, amend, push, tag, dan rewrite history
tetap dilakukan pengguna, bukan agen.
