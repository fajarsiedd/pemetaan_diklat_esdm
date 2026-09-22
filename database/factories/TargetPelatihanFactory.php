<?php

namespace Database\Factories;

use App\Models\Pegawai;
use App\Models\Pelatihan;
use App\Models\TargetPelatihan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TargetPelatihan>
 */
class TargetPelatihanFactory extends Factory
{
    protected $model = TargetPelatihan::class;

    public function definition(): array
    {
        return [
            'pegawai_id' => Pegawai::factory(),
            'pelatihan_id' => Pelatihan::factory(),
            'prioritas' => fake()->numberBetween(1, 4),
            'status' => fake()->randomElement(['ditargetkan', 'sedang_proses', 'selesai']),
        ];
    }
}
