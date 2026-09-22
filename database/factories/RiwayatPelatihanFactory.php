<?php

namespace Database\Factories;

use App\Models\Pegawai;
use App\Models\Pelatihan;
use App\Models\RiwayatPelatihan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiwayatPelatihan>
 */
class RiwayatPelatihanFactory extends Factory
{
    protected $model = RiwayatPelatihan::class;

    public function definition(): array
    {
        return [
            'pegawai_id' => Pegawai::factory(),
            'pelatihan_id' => Pelatihan::factory(),
            'judul_custom' => null,
            'tahun' => (string) fake()->numberBetween(2019, 2026),
            'penyelenggara' => fake()->company(),
            'file_sertifikat' => null,
        ];
    }
}
