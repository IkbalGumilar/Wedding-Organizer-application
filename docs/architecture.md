# Arsitektur Atha Decoration

Dokumen ini menetapkan arah implementasi V1 untuk anggaran sekitar Rp3.000.000.
Rancangan domain di bawah belum berarti fitur tersebut sudah diimplementasikan.
Status pekerjaan dicatat dalam [roadmap](roadmap.md).

## Batas aplikasi

Gunakan satu aplikasi Laravel, satu database MySQL/MariaDB, dan deployment tunggal.
Blade dan Tailwind menyediakan tampilan publik serta pelanggan. Filament menyediakan
admin. Livewire dipakai melalui Filament; komponen tambahan dibuat hanya jika form
Blade biasa tidak mencukupi. Vite membangun aset frontend.

Alur kode: `Routes → Controller / Filament → Action bila diperlukan → Eloquent`.
CRUD sederhana tetap mengikuti Laravel. `CreateBooking` dan `ChangeBookingStatus`
dapat menjadi action kecil ketika aturan bisnisnya diimplementasikan.
Jangan membuat repository layer, DTO framework, permission package, API terpisah,
SPA, microservices, atau abstraksi untuk kebutuhan yang belum ada.

## Identitas dan otorisasi

- Gunakan satu model `User`, guard session `web`, dan flag `is_admin` default `false`.
- Fortify menangani autentikasi pelanggan dengan tampilan Blade. Login admin memakai Filament.
- Registrasi tidak boleh menerima hak admin dari input pengguna. Tidak ada registrasi admin publik.
- `FilamentUser::canAccessPanel()` harus memeriksa akses admin secara eksplisit.
- Daftar/detail booking pelanggan menggunakan query dari relasi pengguna yang login.
- `BookingPolicy` mengizinkan akses hanya kepada pemilik atau administrator berwenang.
- ID yang tidak dimiliki pelanggan menghasilkan 404. ID acak tidak menggantikan otorisasi.
- Policies juga melindungi resource admin dan custom actions; menyembunyikan tombol tidak cukup.

Reset password termasuk V1 dan membutuhkan email pengiriman yang teruji sebelum rilis.
Email verification tidak diwajibkan. Profil cukup `name`, `email`, dan `phone`;
nomor telepon wajib dilengkapi sebelum pengajuan booking.

## Rancangan data

| Model | Data utama dan hubungan |
| --- | --- |
| `User` | Nama, email unique, password hash, telepon, `is_admin`; memiliki banyak booking |
| `WeddingPackage` | Nama, slug unique, deskripsi, harga, status aktif, foto opsional, urutan; memiliki banyak booking |
| `Booking` | Pengguna, paket, tanggal acara, status, catatan, snapshot paket; dimiliki pengguna dan paket |
| `Gallery` | Judul, path foto, deskripsi opsional, urutan, status publikasi; satu record per foto |

Harga disimpan sebagai integer Rupiah utuh, bukan floating point. Tanggal acara memakai
`DATE`; timestamp disimpan UTC. Validasi tanggal mengikuti timezone operasional bisnis.
Status memakai backed enum `BookingStatus`: `pending`, `accepted`, `completed`, `cancelled`.
Foreign key booking ke pengguna/paket memakai `RESTRICT` saat delete. Gunakan deaktivasi
paket untuk menghentikan penawaran tanpa menghapus riwayat. Belum memerlukan soft delete.

Booking menyimpan `package_name_snapshot`, `package_description_snapshot`, dan
`package_price_snapshot`. Server mengambil nilainya dari paket saat pengajuan;
perubahan paket setelahnya tidak mengubah riwayat. `user_id`, status, dan snapshot
tidak dipercaya dari request. Harga yang berubah selama pengisian form perlu dikonfirmasi ulang.

Index awal: email dan slug unique, `(user_id, created_at)`, `(status, event_date)`,
serta index foreign key. Jangan membuat unique global pada `event_date`.
Constraint nilai status dan tes integritas disesuaikan engine database yang dipilih.

## Alur booking yang diusulkan

Booking baru selalu Pending dan belum menjamin tanggal tersedia. Admin dapat mengubah
Pending menjadi Accepted atau Cancelled; Accepted menjadi Completed atau Cancelled.
Completed dan Cancelled bersifat terminal. Completed tidak boleh sebelum tanggal acara.
Transisi memakai otorisasi server dan pembaruan atomik berdasarkan status terakhir.

Default V1: pelanggan meminta perubahan/pembatalan melalui admin. Perubahan tanggal
atau paket dilakukan melalui pembatalan dan pengajuan ulang. Riwayat tidak dihapus.
Paket nonaktif tetap dapat dibaca dalam riwayat melalui snapshot.

## Keputusan bisnis yang belum selesai

Selesaikan sebelum finalisasi skema dan alur booking:

1. Berapa kapasitas acara pada tanggal sama; tetap atau dinilai manual?
2. Kapan booking menjadi Accepted, dan apakah saat itu tanggal dijamin?
3. Apakah harga tetap atau harga awal/estimasi?
4. Apakah lokasi acara atau jumlah tamu wajib untuk menilai permintaan?

Jika kapasitas dibatasi, pengecekan query biasa tidak cukup untuk penerimaan bersamaan.
Pilih constraint atau locking sesuai aturan kapasitas setelah jawaban bisnis tersedia.

## Upload, operasi, dan scope

Foto pemasaran memakai Laravel Storage disk `public`, nama acak, dan upload admin saja.
Validasi isi/MIME JPEG, PNG, WebP; batas awal 2 MiB dan 4096 × 4096 piksel. Tolak SVG
serta executable. Hapus file lama setelah perubahan record berhasil. Status unpublished
hanya mengatur penayangan katalog, bukan membuat file pada disk publik menjadi rahasia.

Tampilkan teks pengguna dengan escaping Blade. Gunakan CSRF, Form Requests, hashing,
throttling, transaksi, dan policies bawaan. Rahasia hanya di environment yang diabaikan Git.
Hosting membutuhkan HTTPS, document root `public/`, storage persisten, email reset password,
serta backup database dan foto dengan uji pemulihan.

V1 mencakup katalog, galeri, kontak/WhatsApp, akun pelanggan, pengajuan/status booking,
CRUD admin, dan statistik sederhana. Home/About/Contact berupa konten tetap.
Pembayaran, WhatsApp API, chat, invoice, vendor, kalender kapasitas kompleks, CMS penuh,
mobile app, dan AI tetap di luar scope. Tidak ada form kontak, Redis, atau worker
permanen tanpa kebutuhan yang terbukti.
