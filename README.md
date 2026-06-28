# RentalPro

Marketplace rental kamera dan perlengkapan produksi berbasis Laravel 10.

## Fitur yang tersedia

- Autentikasi Laravel Breeze dengan akun Customer dan Mitra.
- Role Admin, Mitra, dan Customer dengan middleware serta policy kepemilikan data.
- Landing page, katalog kamera, pencarian/filter, spesifikasi teknis, unit fisik, dan cek ketersediaan.
- Booking dengan validasi stok berdasarkan periode, blackout date, database transaction, dan `lockForUpdate`.
- Dashboard Customer: profil dan identitas privat, riwayat, detail/timeline pesanan, pembayaran, ulasan, dan komplain.
- Dashboard Mitra: profil/dokumen, CRUD produk, booking masuk, dan progres rental.
- Dashboard Admin: user, kategori, verifikasi dokumen mitra, moderasi kamera, booking, pembayaran, dan komplain.
- Seeder data demo, status badge, empty state, flash message, serta layout responsive.

## Kebutuhan

- PHP 8.1
- Composer 2.2+
- MariaDB/MySQL
- Node.js dan npm

## Menjalankan aplikasi

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Buat database MySQL:

```sql
CREATE DATABASE db_rentra_umkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di `.env`, lalu:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Aplikasi tersedia di `http://127.0.0.1:8000`.

## Akun demo

| Role | Email | Password |
|---|---|---|
| Admin | `admin@rentra.test` | `password` |
| Mitra | `mitra@rentra.test` | `password` |
| Customer | `customer@rentra.test` | `password` |

## Pengujian

Pengujian memakai database MySQL terpisah `db_rentra_umkm_test`.

```sql
CREATE DATABASE db_rentra_umkm_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

## Konfigurasi sistem internal

Nilai operasional disimpan pada tabel `system_settings` dan diisi melalui seeder, bukan melalui menu dashboard:

- Tidak ada biaya layanan untuk customer; total awal hanya terdiri dari biaya sewa dan deposit keamanan.
- `trusted_min_score`: ambang skor untuk label Mitra Terpercaya.
- `payment_due_hours`: acuan batas pembayaran untuk proses otomatis.
- `booking_auto_cancel_hours`: acuan pembatalan booking kedaluwarsa.

Pengaturan ini sengaja tidak dapat diubah dari UI admin agar perubahan biaya dan kebijakan harus melalui review kode/deployment. Dua nilai waktu baru menjadi konfigurasi acuan dan memerlukan scheduler jika otomatisasi kedaluwarsa akan diaktifkan.

## Catatan keamanan versi framework

Blueprint mengunci Laravel 10 dan PHP 8.1. Laravel 10 telah melewati masa dukungan keamanan dan Composer saat ini melaporkan advisory pada lini tersebut. Pengecualian `audit.block-insecure` di `composer.json` hanya disediakan agar spesifikasi blueprint dapat dipasang untuk demo lokal. Sebelum deployment publik, upgrade ke Laravel/PHP yang masih didukung dan hapus pengecualian itu.
