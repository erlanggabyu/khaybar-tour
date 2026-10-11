<?php

namespace Database\Factories;

use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pembayaran>
 */
class PembayaranFactory extends Factory
{
    protected $model = Pembayaran::class;

    public function definition(): array
    {
        return [
            'pendaftaran_id' => Pendaftaran::factory(),
            'jenis_pembayaran' => Pembayaran::JENIS_DP,
            'nominal' => 7500000,
            'bukti_file' => null,
            'nama_file' => null,
            'mime_type' => null,
            'ukuran_file' => null,
            'status_pembayaran' => Pembayaran::STATUS_BELUM_BAYAR,
            'catatan_admin' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];
    }
}
