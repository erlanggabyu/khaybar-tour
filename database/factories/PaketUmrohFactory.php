<?php

namespace Database\Factories;

use App\Models\PaketUmroh;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaketUmroh>
 */
class PaketUmrohFactory extends Factory
{
    protected $model = PaketUmroh::class;

    public function definition(): array
    {
        $kuota = fake()->numberBetween(20, 50);

        return [
            'kode_paket' => fake()->unique()->bothify('UMR###??'),
            'nama_paket' => 'Umroh '.fake()->unique()->word(),
            'jenis_perjalanan' => fake()->randomElement(['Hemat', 'Reguler', 'Premium']),
            'durasi' => fake()->numberBetween(9, 14),
            'harga' => fake()->numberBetween(24000000, 40000000),
            'kota_keberangkatan' => fake()->randomElement(['Jakarta', 'Surabaya']),
            'tanggal_keberangkatan' => fake()->dateTimeBetween('+1 month', '+1 year'),
            'kuota_jamaah' => $kuota,
            'sisa_kuota' => $kuota,
            'fasilitas' => "Tiket pesawat\nHotel\nMakan 3 kali sehari",
            'jadwal_perjalanan' => "Hari 1: Keberangkatan\nHari 2: Ibadah",
            'persyaratan_dokumen' => "Paspor\nKTP\nKartu Keluarga",
            'status_paket' => PaketUmroh::STATUS_TERSEDIA,
        ];
    }
}
