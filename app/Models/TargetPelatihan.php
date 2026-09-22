<?php

namespace App\Models;

use Database\Factories\TargetPelatihanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetPelatihan extends Model
{
    /** @use HasFactory<TargetPelatihanFactory> */
    use HasFactory;

    protected $table = 'target_pelatihan';

    protected $fillable = [
        'pegawai_id',
        'pelatihan_id',
        'prioritas',
        'status',
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
