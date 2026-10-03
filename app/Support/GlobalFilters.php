<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class GlobalFilters
{
    public static function active(?string $value): bool
    {
        return $value !== null && $value !== '' && mb_strtolower($value) !== 'all';
    }

    /**
     * Filter an employee (pegawai) query by jabatan and jenjang.
     */
    public static function applyToPegawai(Builder $query, ?string $jabatan, ?string $jenjang): Builder
    {
        return $query
            ->when(self::active($jabatan), fn (Builder $q) => $q->where('pegawai.jabatan', $jabatan))
            ->when(self::active($jenjang), fn (Builder $q) => $q->where('pegawai.jenjang', $jenjang));
    }

    /**
     * Filter a master pelatihan query by the target audience (jabatan/jenjang),
     * keeping general (null/empty) trainings accessible.
     */
    public static function applyToPelatihan(Builder $query, ?string $jabatan, ?string $jenjang): Builder
    {
        $query->when(self::active($jabatan), function (Builder $q) use ($jabatan) {
            $q->where(function (Builder $w) use ($jabatan) {
                $w->whereJsonContains('target_jabatan', $jabatan)
                    ->orWhereNull('target_jabatan')
                    ->orWhere('target_jabatan', '[]');
            });
        });

        return $query->when(self::active($jenjang), function (Builder $q) use ($jenjang) {
            $q->where(function (Builder $w) use ($jenjang) {
                $w->where('jenjang', $jenjang)
                    ->orWhereNull('jenjang')
                    ->orWhere('jenjang', '');
            });
        });
    }

    /**
     * Filter a pelatihan (with targets) query so only trainings whose assigned
     * employees match the selected jabatan/jenjang are returned.
     */
    public static function applyToTrainingTargets(Builder $query, ?string $jabatan, ?string $jenjang): Builder
    {
        $query->when(self::active($jabatan), function (Builder $q) use ($jabatan) {
            $q->whereHas('targetPelatihan.pegawai', fn (Builder $w) => $w->where('jabatan', $jabatan));
        });

        return $query->when(self::active($jenjang), function (Builder $q) use ($jenjang) {
            $q->whereHas('targetPelatihan.pegawai', fn (Builder $w) => $w->where('jenjang', $jenjang));
        });
    }
}