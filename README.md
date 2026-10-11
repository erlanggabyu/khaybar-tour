# Sistem Pendaftaran Umroh Khaybar Tour

Aplikasi web Laravel untuk mengelola proses pendaftaran perjalanan umroh dari pemilihan paket sampai invoice dan laporan admin.

## Role Pengguna

- **Pelanggan**: memilih paket, mendaftar, mengelola jamaah dan dokumen, mengunggah pembayaran, serta melihat invoice.
- **Admin**: mengelola paket, melihat pendaftaran/jamaah, memverifikasi dokumen dan pembayaran, serta mencetak laporan.

Tidak ada role finance atau manajemen. Seluruh verifikasi dan laporan dikelola admin.

## Fitur Pelanggan

- Registrasi, login, logout, dan dashboard pelanggan.
- Melihat paket tersedia dan detail paket.
- Mendaftar paket, melihat status, dan membatalkan sebelum pembayaran DP.
- Mengelola data jamaah sesuai jumlah pada pendaftaran.
- Mengunggah dokumen PDF/JPG/JPEG/PNG maksimal 2 MB.
- Mengunggah bukti DP dan pelunasan serta melihat status verifikasi.
- Melihat dan mencetak invoice milik sendiri.

## Fitur Admin

- Dashboard ringkasan operasional.
- CRUD paket umroh dan perubahan status paket.
- Melihat seluruh pendaftaran dan data jamaah.
- Verifikasi dokumen dengan status Valid, Tidak Valid, atau Perlu Revisi.
- Verifikasi DP dan pelunasan serta memperbarui status pendaftaran.
- Laporan pendaftaran, pembayaran, dan rekap jamaah per paket dengan filter dan cetak.

## Teknologi

- PHP 8.2+, Laravel 13, MySQL
- Blade dan Bootstrap 5
- PHPUnit dengan SQLite in-memory untuk testing

## Instalasi

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Konfigurasikan database MySQL pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=khaybar_tour
DB_USERNAME=root
DB_PASSWORD=
```

Untuk produksi/demo publik, gunakan `APP_DEBUG=false` agar detail error teknis tidak tampil.

## Migration dan Seeder

```bash
php artisan migrate:fresh --seed
```

Seeder membuat paket `UMR001` sampai `UMR005` dan akun demo berikut:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@khaybartour.com` | `admin123` |
| Pelanggan | `ahmad@gmail.com` | `pelanggan123` |

## Menjalankan Aplikasi

```bash
php artisan optimize:clear
php artisan serve
```

Buka `http://127.0.0.1:8000`. Jalankan hanya satu proses `php artisan serve` pada port tersebut.

## Menjalankan Test

```bash
php artisan test
```

Test menggunakan SQLite in-memory, sehingga tidak mengubah database MySQL aplikasi.

## Checklist Testing Manual

- [ ] Pelanggan dapat registrasi, login, diarahkan ke dashboard pelanggan, dan logout.
- [ ] Admin dapat login dan diarahkan ke dashboard admin.
- [ ] Guest diarahkan ke login saat membuka fitur privat.
- [ ] Pelanggan tidak dapat membuka halaman admin atau data pelanggan lain.
- [ ] Pelanggan dapat melihat paket tersedia, detail paket, dan mendaftar selama kuota tersedia.
- [ ] Pelanggan dapat mengisi jamaah tanpa melebihi jumlah jamaah pendaftaran.
- [ ] Pelanggan dapat mengunggah dokumen yang valid dan admin dapat memverifikasinya.
- [ ] File PHP, JS, EXE, HTML, dan SVG serta file di atas 2 MB ditolak.
- [ ] Pelanggan dapat mengunggah DP; setelah admin menyatakan valid, pelunasan dapat diunggah.
- [ ] Setelah pelunasan valid, status pendaftaran menjadi Lunas.
- [ ] Pelanggan hanya dapat melihat invoice miliknya.
- [ ] Admin dapat memfilter dan mencetak laporan.
- [ ] Semua form perubahan data memiliki CSRF token dan validasi backend.

## Catatan Keamanan

- Password disimpan menggunakan hash Laravel.
- Upload disimpan pada disk private dengan nama acak dan hanya dapat diunduh pemilik/admin.
- Route privat dilindungi middleware `auth` dan `role`, ditambah pemeriksaan ownership.
- Login dan registrasi dibatasi maksimal lima percobaan per menit per sumber.
- Respons web mengirim header keamanan dasar dan halaman error ramah pengguna.
