<?php

namespace Database\Factories;

use App\Models\Jamaah;
use App\Models\Pendaftaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jamaah>
 */
class JamaahFactory extends Factory
{
    protected $model = Jamaah::class;

    public function definition(): array
    {
        return [
            'pendaftaran_id' => Pendaftaran::factory(),
            'nama_lengkap' => fake()->name(),
            'nik' => fake()->unique()->numerify('################'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->dateTimeBetween('-70 years', '-10 years'),
            'alamat' => fake()->address(),
            'no_hp' => '08'.fake()->numerify('##########'),
            'email' => fake()->safeEmail(),
            'no_paspor' => null,
            'tanggal_terbit_paspor' => null,
            'tanggal_berakhir_paspor' => null,
        ];
    }
}
