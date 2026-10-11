<?php

namespace Database\Factories;

use App\Models\PaketUmroh;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pendaftaran>
 */
class PendaftaranFactory extends Factory
{
    protected $model = Pendaftaran::class;

    public function definition(): array
    {
        return [
            'kode_pendaftaran' => 'DFT-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'user_id' => User::factory()->state(['role' => 'pelanggan']),
            'paket_umroh_id' => PaketUmroh::factory(),
            'tanggal_pendaftaran' => now()->toDateString(),
            'jumlah_jamaah' => 1,
            'total_harga' => 25000000,
            'catatan_pelanggan' => null,
            'status_pendaftaran' => Pendaftaran::STATUS_MENUNGGU_DP,
        ];
    }
}
