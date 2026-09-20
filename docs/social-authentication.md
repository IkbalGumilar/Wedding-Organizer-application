# Social authentication

Social login memakai Laravel Socialite dan provider maintained yang sudah ada di
`composer.lock`. Email/password Fortify tetap menjadi metode login utama dan social
user tidak diwajibkan memiliki password.

Provider hanya ditampilkan jika `*_ENABLED=true` dan credential minimum lengkap.
Pengecekan ini dipusatkan pada `App\Enums\SocialProvider::enabled()`; jangan membaca
`env()` langsung dari Blade atau controller.

## Environment umum

Pastikan `.env` memiliki `APP_URL` yang sesuai origin aplikasi dan `APP_KEY` yang stabil.
Redirect URI di bawah harus memakai origin yang sama persis dengan aplikasi.

```dotenv
GOOGLE_ENABLED=false
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

APPLE_ENABLED=false
APPLE_CLIENT_ID=
APPLE_CLIENT_SECRET=
APPLE_KEY_ID=
APPLE_TEAM_ID=
APPLE_PRIVATE_KEY=
APPLE_REDIRECT_URI="${APP_URL}/auth/apple/callback"
APPLE_LINK_REDIRECT_URI="${APP_URL}/auth/apple/link/callback"

MICROSOFT_ENABLED=false
MICROSOFT_CLIENT_ID=
MICROSOFT_CLIENT_SECRET=
MICROSOFT_TENANT_ID=common
MICROSOFT_REDIRECT_URI="${APP_URL}/auth/microsoft/callback"

FACEBOOK_ENABLED=false
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI="${APP_URL}/auth/facebook/callback"

X_ENABLED=false
X_CLIENT_ID=
X_CLIENT_SECRET=
X_REDIRECT_URI="${APP_URL}/auth/x/callback"
```

Jangan menaruh secret nyata pada dokumentasi, `.env.example`, source code, atau
test. Setelah mengubah `.env` pada deployment dengan config cache, jalankan
`php artisan config:clear` atau rebuild config cache.

## Google

1. Buka Google Cloud Console dan buat project atau pilih project aplikasi.
2. Aktifkan OAuth consent screen dan buat OAuth Client ID tipe Web application.
3. Masukkan `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` ke `.env`.
4. Daftarkan Authorized redirect URI:

   - Development: `http://127.0.0.1:8000/auth/google/callback`
   - Production: `https://domain-anda.example/auth/google/callback`

5. Set `GOOGLE_ENABLED=true`.

Scope login hanya memakai identitas dasar provider. Email yang tidak menyatakan
verified tidak dijadikan email login lokal. Jika email sudah dimiliki akun lokal,
user harus login dengan email/password lalu menghubungkan Google dari profil.

## Microsoft

1. Buka Microsoft Entra admin center dan buat App registration.
2. Tambahkan Web redirect URI pada bagian Authentication.
3. Buat client secret dan simpan nilainya hanya pada `MICROSOFT_CLIENT_SECRET`.
4. Isi `MICROSOFT_CLIENT_ID`, `MICROSOFT_TENANT_ID`, dan
   `MICROSOFT_REDIRECT_URI`.
5. Set `MICROSOFT_ENABLED=true`.

Gunakan tenant `common` untuk akun Microsoft personal dan organisasi jika memang
dibutuhkan. Flow meminta OpenID Connect `openid profile User.Read` untuk mengambil
subject dan nama. Tidak ada permission Graph tambahan yang diminta. Subject dari
ID token divalidasi oleh provider sebelum profile dipakai.

Redirect URI:

- Development: `http://127.0.0.1:8000/auth/microsoft/callback`
- Production: `https://domain-anda.example/auth/microsoft/callback`

## Facebook

1. Buka Meta for Developers, buat App, lalu tambahkan Facebook Login.
2. Isi App ID dan App Secret pada `FACEBOOK_CLIENT_ID` dan
   `FACEBOOK_CLIENT_SECRET`.
3. Tambahkan Valid OAuth Redirect URI:

   - Development: `http://127.0.0.1:8000/auth/facebook/callback`
   - Production: `https://domain-anda.example/auth/facebook/callback`

4. Set `FACEBOOK_ENABLED=true`.

Scope dan fields dibatasi pada data authentication yang diperlukan: provider ID,
nama, dan email jika tersedia. Jangan menambahkan permission marketing, friends,
atau profile tambahan untuk flow ini.

## Apple

Apple Sign in memerlukan Services ID/Client ID, Team ID, dan HTTPS pada production.
Callback Apple memakai `form_post`; flow yang ada mempertahankan nonce dan validasi
token. Apple dapat mengirim private relay email dan nama hanya pada login pertama.
Provider ID tetap menjadi identitas utama.

Pilih salah satu konfigurasi:

- `APPLE_CLIENT_SECRET` yang dibuat dari Apple Developer, atau
- konfigurasi key-based: `APPLE_KEY_ID`, `APPLE_TEAM_ID`, dan `APPLE_PRIVATE_KEY`.

`APPLE_PRIVATE_KEY` dapat berupa path private key yang aman pada server atau isi key
sesuai dukungan provider. Jangan menyimpan file/private key di repository.

Daftarkan kedua callback berikut pada Apple Developer:

- Login: `https://domain-anda.example/auth/apple/callback`
- Account linking: `https://domain-anda.example/auth/apple/link/callback`

Untuk development, gunakan HTTPS tunnel/domain yang dapat diakses Apple. Cookie
nonce bersifat Secure dan tidak dapat diandalkan pada HTTP localhost biasa.
Setelah semua nilai diisi, set `APPLE_ENABLED=true`. Account linking Apple tetap
memerlukan intent, session binding, dan konfirmasi eksplisit yang sudah ada.

## X / Twitter

X memakai X OAuth 2 provider bawaan Laravel Socialite, bukan implementasi OAuth baru.

1. Buka X Developer Portal dan buat Project/App.
2. Aktifkan OAuth 2.0 dan pilih Web App sebagai application type.
3. Isi `X_CLIENT_ID`, `X_CLIENT_SECRET`, dan `X_REDIRECT_URI`.
4. Daftarkan callback:

   - Development: `http://127.0.0.1:8000/auth/x/callback`
   - Production: `https://domain-anda.example/auth/x/callback`

5. Set `X_ENABLED=true`.

Scope yang dipakai hanya `users.read`. X dapat tidak menyediakan email; kondisi itu
didukung karena social account dapat dibuat tanpa email/password lokal.

## Testing setelah konfigurasi

Jalankan pemeriksaan lokal:

```bash
php artisan config:clear
php artisan route:list
php artisan test --compact
npm run build
```

Untuk setiap provider yang sudah dikonfigurasi, uji manual:

1. Buka `/login` dan klik tombol provider.
2. Selesaikan consent provider.
3. Pastikan callback kembali ke `/dashboard` atau `/profil` jika WhatsApp kosong.
4. Logout, lalu login lagi dengan provider yang sama.
5. Pastikan user dan `social_accounts` yang sama dipakai, tanpa duplicate user.
6. Uji akun email/password yang emailnya sama; aplikasi harus meminta login lalu
   explicit linking, bukan membuat auto-link.

Automated test memakai fake provider dan tidak memanggil provider nyata. Credentials
provider yang belum tersedia tetap aman karena tombolnya tidak dirender.

## Troubleshooting Google `invalid_client`

Jika Google menampilkan `401 invalid_client` atau `The OAuth client was not found`,
periksa hal berikut sebelum mengubah kode aplikasi:

1. `GOOGLE_CLIENT_ID` berasal dari OAuth 2.0 Client ID dengan tipe **Web application**;
   API key, client Android, dan client Desktop tidak dapat menggantikannya.
2. Nilai client ID harus berakhiran `.apps.googleusercontent.com` dan client secret
   harus berasal dari client yang sama pada project Google Cloud.
3. Authorized redirect URI harus sama persis dengan nilai aplikasi. Untuk server lokal
   saat ini gunakan:

   `http://127.0.0.1:8000/auth/google/callback`

   `localhost` dan `127.0.0.1` adalah origin berbeda.
4. Setelah memperbaiki `.env`, jalankan `php artisan optimize:clear` lalu buka ulang
   halaman login. Provider dengan konfigurasi invalid akan disembunyikan oleh aplikasi.

Jangan menaruh client secret di repository atau mengirimkannya pada laporan error.
