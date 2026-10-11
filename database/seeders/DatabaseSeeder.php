<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\PaketUmroh;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminUser = User::create([
            'name' => 'Admin Khaybar Tour',
            'email' => 'admin@khaybartour.com',
            'password' => Hash::make('admin123'),
            'no_hp' => '081234567890',
            'role' => 'admin',
        ]);

        Admin::create([
            'user_id' => $adminUser->id,
            'jabatan' => 'Administrator',
        ]);

        $pelangganUser = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad@gmail.com',
            'password' => Hash::make('pelanggan123'),
            'no_hp' => '087766554433',
            'role' => 'pelanggan',
        ]);

        Pelanggan::create([
            'user_id' => $pelangganUser->id,
        ]);

        $pakets = [
            ['UMR001', 'Umroh Hemat Berfaidah', 'Hemat', 9, 24500000, 'Jakarta', '2026-08-15', 45],
            ['UMR002', 'Umroh Reguler', 'Reguler', 9, 27500000, 'Jakarta', '2026-09-12', 45],
            ['UMR003', 'Umroh Nyaman', 'Nyaman', 12, 31000000, 'Surabaya', '2026-10-10', 40],
            ['UMR004', 'Umroh Plus Thaif', 'Plus Thaif', 12, 33500000, 'Jakarta', '2026-11-07', 40],
            ['UMR005', 'Umroh Premium', 'Premium', 13, 36000000, 'Surabaya', '2026-12-05', 30],
        ];

        foreach ($pakets as [$kode, $nama, $jenis, $durasi, $harga, $kota, $tanggal, $kuota]) {
            PaketUmroh::create([
                'kode_paket' => $kode,
                'nama_paket' => $nama,
                'jenis_perjalanan' => $jenis,
                'durasi' => $durasi,
                'harga' => $harga,
                'kota_keberangkatan' => $kota,
                'tanggal_keberangkatan' => $tanggal,
                'kuota_jamaah' => $kuota,
                'sisa_kuota' => $kuota,
                'status_paket' => PaketUmroh::STATUS_TERSEDIA,
                'fasilitas' => "Tiket pesawat pulang-pergi\nHotel Mekkah dan Madinah\nMakan 3 kali sehari\nTransportasi bus AC\nPendamping ibadah",
                'jadwal_perjalanan' => "Hari 1: Keberangkatan ke Arab Saudi\nHari 2-5: Ibadah dan ziarah di Madinah\nHari 6-8: Ibadah umroh dan ziarah di Mekkah\nHari terakhir: Kembali ke Indonesia",
                'persyaratan_dokumen' => "Paspor dengan masa berlaku minimal 6 bulan\nKTP dan Kartu Keluarga\nPasfoto latar belakang putih\nDokumen kesehatan sesuai ketentuan",
            ]);
        }
    }
}
