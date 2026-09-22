<?php

namespace App\Models;

use Database\Factories\PegawaiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    /** @use HasFactory<PegawaiFactory> */
    use HasFactory;

    protected $table = 'pegawai';

    public const JENJANG_OPTIONS = ['Ahli Pertama', 'Ahli Muda', 'Ahli Madya', 'Ahli Madya-Pusaka'];

    protected $fillable = [
        'user_id',
        'nama',
        'nip',
        'jabatan',
        'jenjang',
        'unit_kerja',
        'provinsi',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
