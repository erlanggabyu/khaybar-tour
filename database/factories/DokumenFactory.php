<?php

namespace Database\Factories;

use App\Models\Dokumen;
use App\Models\Jamaah;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Dokumen>
 */
class DokumenFactory extends Factory
{
    protected $model = Dokumen::class;

    public function definition(): array
    {
        return [
            'jamaah_id' => Jamaah::factory(),
            'pendaftaran_id' => fn (array $attributes) => Jamaah::find($attributes['jamaah_id'])->pendaftaran_id,
            'jenis_dokumen' => fake()->randomElement(Dokumen::documentTypes()),
            'nama_file' => 'dokumen.pdf',
            'path_file' => 'dokumen/'.Str::uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'ukuran_file' => 102400,
            'status_dokumen' => Dokumen::STATUS_MENUNGGU,
            'catatan_admin' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];
    }
}
