<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\Pelatihan;
use App\Models\RiwayatPelatihan;
use App\Models\User;
use App\Services\TargetPelatihanService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => '123456',
                'role' => 'admin',
            ],
        );

        $pegawaiUsers = [
            [
                'name' => 'Bambang Sutrisno',
                'email' => 'bambang@example.com',
                'pegawai' => [
                    'nip' => '198502152010011001',
                    'jabatan' => 'Inspektur Ketenagalistrikan',
                    'jenjang' => 'Ahli Muda',
                    'unit_kerja' => 'Unit Pelaksana Transmisi Jawa Barat',
                    'provinsi' => 'Jawa Barat',
                ],
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti@example.com',
                'pegawai' => [
                    'nip' => '199003202012022002',
                    'jabatan' => 'Penyelidik Bumi',
                    'jenjang' => 'Ahli Pertama',
                    'unit_kerja' => 'Unit Pelaksana Pengendalian Pembangkitan Sumatera',
                    'provinsi' => 'Sumatera Barat',
                ],
            ],
        ];

        $riwayat = [];

        foreach ($pegawaiUsers as $index => $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => '123456',
                    'role' => 'pegawai',
                ],
            );

            $pegawai = Pegawai::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama' => $data['name'],
                    'nip' => $data['pegawai']['nip'],
                    'jabatan' => $data['pegawai']['jabatan'],
                    'jenjang' => $data['pegawai']['jenjang'],
                    'unit_kerja' => $data['pegawai']['unit_kerja'],
                    'provinsi' => $data['pegawai']['provinsi'],
                ],
            );

            $riwayat[$index] = $pegawai;
        }

        $pelatihans = [
            [
                'kode_diklat' => 'IK-MUDA-001',
                'judul' => 'Pelatihan Inspeksi Ketenagalistrikan Tingkat Dasar',
                'jenjang' => 'Ahli Muda',
                'subsektor' => 'Transmisi',
                'target_jabatan' => ['Inspektur Ketenagalistrikan'],
                'kategori' => 'technical',
                'status' => 'wajib',
            ],
            [
                'kode_diklat' => 'IK-MUDA-002',
                'judul' => 'Keselamatan Ketenagalistrikan (K2) untuk Inspektur Ahli Muda',
                'jenjang' => 'Ahli Muda',
                'subsektor' => 'Distribusi',
                'target_jabatan' => ['Inspektur Ketenagalistrikan'],
                'kategori' => 'legal',
                'status' => 'wajib',
            ],
            [
                'kode_diklat' => 'IK-MUDA-003',
                'judul' => 'Teknik Audit Pemanfaatan Tenaga Listrik',
                'jenjang' => 'Ahli Muda',
                'subsektor' => 'Pembangkitan',
                'target_jabatan' => ['Inspektur Ketenagalistrikan'],
                'kategori' => 'commercial',
                'status' => 'opsional',
            ],
            [
                'kode_diklat' => 'PB-PRT-001',
                'judul' => 'Penyelidikan Bumi Pertambangan Tingkat Dasar',
                'jenjang' => 'Ahli Pertama',
                'subsektor' => 'Pertambangan',
                'target_jabatan' => ['Penyelidik Bumi'],
                'kategori' => 'technical',
                'status' => 'wajib',
            ],
            [
                'kode_diklat' => 'PB-PRT-002',
                'judul' => 'Geologi Terapan untuk Penyelidik Bumi',
                'jenjang' => 'Ahli Pertama',
                'subsektor' => 'Eksplorasi',
                'target_jabatan' => ['Penyelidik Bumi', 'Inspektur Ketenagalistrikan'],
                'kategori' => 'technical',
                'status' => 'opsional',
            ],
            [
                'kode_diklat' => 'IK-PRT-004',
                'judul' => 'Komunikasi Efektif dan Etika Profesi Inspektur',
                'jenjang' => 'Ahli Pertama',
                'subsektor' => 'Umum',
                'target_jabatan' => ['Inspektur Ketenagalistrikan'],
                'kategori' => 'soft_skill',
                'status' => 'opsional',
            ],
        ];

        foreach ($pelatihans as $data) {
            Pelatihan::query()->updateOrCreate(
                ['kode_diklat' => $data['kode_diklat']],
                $data,
            );
        }

        $dasar = Pelatihan::where('kode_diklat', 'IK-MUDA-001')->first();

        if ($dasar) {
            RiwayatPelatihan::query()->updateOrCreate(
                [
                    'pegawai_id' => $riwayat[0]->id,
                    'pelatihan_id' => $dasar->id,
                ],
                [
                    'judul_custom' => $dasar->judul,
                    'tahun' => '2023',
                    'penyelenggara' => 'Pusdiklat Ketenagalistrikan',
                    'file_sertifikat' => null,
                ],
            );
        }

        app(TargetPelatihanService::class)->recalculate();
    }
}
