<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PegawaiImport implements ToCollection, WithHeadingRow
{
    /**
     * Import the pegawai master data and auto-generate/bind user accounts.
     *
     * Expected columns (with heading row): No, Nama Lengkap, NIP, Jabatan,
     * Jenjang, Unit Kerja, Provinsi. Heading keys are normalised to
     * snake_case (e.g. "Nama Lengkap" -> nama_lengkap).
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $nip = trim((string) ($row['nip'] ?? ''));

                if ($nip === '') {
                    continue;
                }

                $nama = trim((string) ($row['nama_lengkap'] ?? ''));
                $email = $nip . '@mail.com';

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $nama,
                        'password' => Hash::make('123456'),
                        'role' => 'pegawai',
                    ],
                );

                Pegawai::updateOrCreate(
                    ['nip' => $nip],
                    [
                        'user_id' => $user->id,
                        'nama' => $nama,
                        'jabatan' => trim((string) ($row['jabatan'] ?? '')),
                        'jenjang' => trim((string) ($row['jenjang'] ?? '')),
                        'unit_kerja' => trim((string) ($row['unit_kerja'] ?? '')),
                        'provinsi' => trim((string) ($row['provinsi'] ?? '')),
                    ],
                );
            }
        });
    }
}