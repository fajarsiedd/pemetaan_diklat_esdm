<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelatihan extends Model
{
    /** @use HasFactory<\Database\Factories\PelatihanFactory> */
    use HasFactory;

    protected $table = 'pelatihan';

    public const KATEGORI_OPTIONS = ['technical', 'legal', 'commercial', 'soft_skill'];

    public const STATUS_OPTIONS = ['wajib', 'opsional'];

    public const TARGET_JABATAN_OPTIONS = ['Inspektur Ketenagalistrikan', 'Penyelidik Bumi'];

    public const JENJANG_OPTIONS = ['Ahli Pertama', 'Ahli Muda', 'Ahli Madya', 'Ahli Madya-Pusaka'];

    protected $fillable = [
        'kode_diklat',
        'judul',
        'jenjang',
        'subsektor',
        'target_jabatan',
        'kategori',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_jabatan' => 'array',
        ];
    }

    public function targetPelatihan(): HasMany
    {
        return $this->hasMany(TargetPelatihan::class);
    }

    public function riwayatPelatihan(): HasMany
    {
        return $this->hasMany(RiwayatPelatihan::class);
    }
}