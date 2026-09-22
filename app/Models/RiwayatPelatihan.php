<?php

namespace App\Models;

use Database\Factories\RiwayatPelatihanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPelatihan extends Model
{
    /** @use HasFactory<RiwayatPelatihanFactory> */
    use HasFactory;

    protected $table = 'riwayat_pelatihan';

    protected $fillable = [
        'pegawai_id',
        'pelatihan_id',
        'judul_custom',
        'tahun',
        'penyelenggara',
        'file_sertifikat',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function pelatihan(): BelongsTo
    {
        return $this->belongsTo(Pelatihan::class);
    }
}
