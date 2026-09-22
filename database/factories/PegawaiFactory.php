<?php

namespace Database\Factories;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pegawai>
 */
class PegawaiFactory extends Factory
{
    protected $model = Pegawai::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nama' => fake()->name(),
            'nip' => fake()->unique()->numerify('###############'),
            'jabatan' => fake()->randomElement(['Inspektur Ketenagalistrikan', 'Penyelidik Bumi']),
            'jenjang' => fake()->randomElement(['Ahli Muda', 'Ahli Madya', 'Ahli Pertama', 'Ahli Madya-Pusaka']),
            'unit_kerja' => fake()->company(),
            'provinsi' => fake()->state(),
        ];
    }
}
