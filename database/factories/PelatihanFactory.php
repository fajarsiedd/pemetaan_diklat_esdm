<?php

namespace Database\Factories;

use App\Models\Pelatihan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pelatihan>
 */
class PelatihanFactory extends Factory
{
    protected $model = Pelatihan::class;

    public function definition(): array
    {
        return [
            'kode_diklat' => fake()->unique()->bothify('DIKLAT-####'),
            'judul' => fake()->sentence(5),
            'jenjang' => fake()->optional()->randomElement(['Ahli Muda', 'Ahli Madya', 'Ahli Pertama', 'Ahli Madya-Pusaka']),
            'subsektor' => fake()->randomElement(['Subsektor Transmisi', 'Subsektor Distribusi', 'Subsektor Gardu Induk', 'Pembangkitan']),
            'target_jabatan' => ['Inspektur Ketenagalistrikan', 'Penyelidik Bumi'],
            'kategori' => fake()->randomElement(Pelatihan::KATEGORI_OPTIONS),
            'status' => fake()->randomElement(Pelatihan::STATUS_OPTIONS),
        ];
    }
}