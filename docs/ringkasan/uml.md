\# Ringkasan UML Sistem Pendaftaran Umroh Khaybar Tour



\## Aktor Sistem

Sistem hanya menggunakan tiga aktor utama, yaitu:



1\. Calon Pelanggan

2\. Pelanggan

3\. Admin



Aktor Finance dan Manajemen tidak digunakan karena bersifat opsional. Seluruh proses verifikasi pembayaran, pengelolaan laporan, dan perubahan status dilakukan oleh Admin.



\## Hak Akses Aktor



\### Calon Pelanggan

\- Melihat daftar paket umroh

\- Melihat detail paket umroh

\- Melakukan registrasi akun



\### Pelanggan

\- Login ke sistem

\- Memilih paket umroh

\- Melakukan pendaftaran paket umroh

\- Mengisi data jamaah

\- Mengunggah dokumen jamaah

\- Melakukan pembayaran DP

\- Mengunggah bukti pembayaran

\- Melakukan pelunasan

\- Melihat status pendaftaran

\- Melihat status dokumen

\- Melihat status pembayaran

\- Mengunduh invoice



\### Admin

\- Login ke sistem

\- Mengelola data paket umroh

\- Menambah paket baru

\- Mengubah data paket

\- Menonaktifkan paket

\- Melihat daftar pendaftar

\- Memeriksa detail data jamaah

\- Memverifikasi dokumen pelanggan

\- Memberikan catatan revisi dokumen

\- Memverifikasi pembayaran DP

\- Memverifikasi pembayaran pelunasan

\- Mengubah status pendaftaran

\- Mengubah status keberangkatan

\- Mengelola data pelanggan

\- Mencetak laporan pendaftaran

\- Mencetak laporan pembayaran

\- Melihat rekap jumlah jamaah per paket



\## Modul Aplikasi

1\. Login dan registrasi

2\. Paket umroh

3\. Pendaftaran paket umroh

4\. Data jamaah

5\. Upload dokumen

6\. Pembayaran DP dan pelunasan

7\. Verifikasi dokumen oleh admin

8\. Verifikasi pembayaran oleh admin

9\. Invoice

10\. Laporan admin



\## Class Utama

\- User

\- Pelanggan

\- Admin

\- PaketTour

\- PaketUmroh

\- Pendaftaran

\- Jamaah

\- Dokumen

\- Pembayaran

\- Invoice

\- Keberangkatan

\- Laporan



\## Relasi Penting

\- Satu pelanggan dapat memiliki banyak pendaftaran.

\- Satu pendaftaran hanya memilih satu paket umroh.

\- Satu pendaftaran dapat memiliki banyak jamaah.

\- Satu jamaah dapat memiliki banyak dokumen.

\- Satu pendaftaran dapat memiliki beberapa pembayaran.

\- Satu pembayaran dapat menghasilkan satu invoice.

\- Satu admin dapat memverifikasi banyak dokumen.

\- Satu admin dapat memverifikasi banyak pembayaran.

\- Satu admin dapat mencetak banyak laporan.



\## Status Pendaftaran

\- Draft

\- Menunggu Pembayaran DP

\- Menunggu Verifikasi Pembayaran

\- DP Terverifikasi

\- Dokumen Belum Lengkap

\- Dokumen Perlu Revisi

\- Dokumen Lengkap

\- Menunggu Pelunasan

\- Lunas

\- Siap Berangkat

\- Berangkat

\- Selesai

\- Dibatalkan



\## Status Pembayaran

\- Belum Bayar

\- Menunggu Verifikasi

\- Valid

\- Tidak Valid

\- Dikembalikan untuk Perbaikan



\## Status Dokumen

\- Belum Upload

\- Menunggu Pemeriksaan

\- Valid

\- Tidak Valid

\- Perlu Revisi



\## Teknologi

\- PHP 8+

\- Laravel

\- MySQL

\- HTML

\- CSS

\- JavaScript

\- Bootstrap

\- Apache atau Nginx

\- Browser sebagai client

\- Folder upload untuk dokumen dan bukti pembayaran

