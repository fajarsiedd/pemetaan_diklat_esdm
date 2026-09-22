<?php

namespace App\Imports;

use App\Models\Pelatihan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PelatihanImport implements ToCollection, WithHeadingRow
{
    /**
     * Import the master training (pelatihan) rows.
     *
     * Expected columns (with heading row): id, jabatan, jenjang, kode_diklat,
     * judul, status, subsektor, kategori.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $kodeDiklat = trim((string) ($row['kode_diklat'] ?? ''));
            $judul = trim((string) ($row['judul'] ?? ''));

            if ($kodeDiklat === '' || $judul === '') {
                continue;
            }

            Pelatihan::updateOrCreate(
                ['kode_diklat' => $kodeDiklat],
                [
                    'judul' => $judul,
                    'target_jabatan' => $this->jabatanToArray($row['jabatan'] ?? null),
                    'jenjang' => $this->jenjangOrNull($row['jenjang'] ?? null),
                    'status' => $this->statusKey($row['status'] ?? null),
                    'subsektor' => trim((string) ($row['subsektor'] ?? '')),
                    'kategori' => $this->kategoriKey($row['kategori'] ?? null),
                ],
            );
        }
    }

    /**
     * The `jabatan` column maps to the `target_jabatan` JSON array column.
     *
     * @return array<int, string>
     */
    private function jabatanToArray(mixed $value): array
    {
        $jabatan = trim((string) $value);

        return $jabatan === '' ? [] : [$jabatan];
    }

    /**
     * An empty `jenjang` cell means a general/umum training for all ranks.
     */
    private function jenjangOrNull(mixed $value): ?string
    {
        $jenjang = trim((string) $value);

        return $jenjang === '' ? null : $jenjang;
    }

    private function statusKey(mixed $value): string
    {
        $status = strtolower(trim((string) $value));

        return in_array($status, Pelatihan::STATUS_OPTIONS, true) ? $status : 'opsional';
    }

    /**
     * Convert the single-letter code (or full label) into the kategori key.
     *
     * T -> technical, L -> legal, C -> commercial, S -> soft_skill.
     */
    private function kategoriKey(mixed $value): string
    {
        $kategori = strtoupper(trim((string) $value));

        return match ($kategori) {
            'T', 'TECHNICAL' => 'technical',
            'L', 'LEGAL' => 'legal',
            'C', 'COMMERCIAL' => 'commercial',
            'S', 'SOFT SKILL', 'SOFT_SKILL', 'SOFTSKILL' => 'soft_skill',
            default => 'technical',
        };
    }
}