<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Pendaftaran;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'pendaftaran_id' => Pendaftaran::factory(),
            'kode_invoice' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'tanggal_invoice' => now()->toDateString(),
            'total_tagihan' => 25000000,
            'total_dibayar' => 0,
            'status_invoice' => Invoice::STATUS_BELUM_LUNAS,
        ];
    }
}
