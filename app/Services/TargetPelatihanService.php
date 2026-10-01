<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Pelatihan;
use App\Models\RiwayatPelatihan;
use App\Models\TargetPelatihan;
use Illuminate\Support\Collection;

class TargetPelatihanService
{
    public const MAX_TARGETS = 4;

    /**
     * Update a target status. When set to 'selesai' the item is moved to
     * riwayat_pelatihan and the freed slot is immediately filled with the
     * next highest-priority training from the employee's cohort.
     */
    public function complete(TargetPelatihan $target, string $status): void
    {
        if ($status !== 'selesai') {
            $target->update(['status' => 'sedang_proses']);

            return;
        }

        $pegawai = $target->pegawai;

        RiwayatPelatihan::create([
            'pegawai_id' => $target->pegawai_id,
            'pelatihan_id' => $target->pelatihan_id,
            'judul_custom' => $target->pelatihan?->judul,
            'tahun' => (string) now()->year,
            'penyelenggara' => null,
            'file_sertifikat' => null,
        ]);

        $target->delete();

        $this->fillForEmployee($pegawai);
    }

    /**
     * Recalculate training targets for all employees.
     *
     * Existing non-completed targets are cleared and re-assigned from
     * scratch based on the current completion gap per cohort group
     * (grouped by jabatan + jenjang).
     *
     * @return array{groups: int, assigned: int, skipped_full: int}
     */
    public function recalculate(): array
    {
        TargetPelatihan::where('status', '!=', 'selesai')->delete();

        $groups = Pegawai::all()->groupBy(fn (Pegawai $pegawai) => $this->groupKey($pegawai));
        $stats = ['groups' => $groups->count(), 'assigned' => 0, 'skipped_full' => 0];

        foreach ($groups as $key => $pegawaiGroup) {
            $jenjang = $this->extractJenjang($key);
            $priorityOrder = $this->priorityOrderFor($pegawaiGroup, $jenjang);

            foreach ($pegawaiGroup as $pegawai) {
                $stats = $this->assignSlots($pegawai, $priorityOrder, $stats);
            }
        }

        return $stats;
    }

    /**
     * Top-up the available target slots (up to MAX_TARGETS) for a single
     * employee using the current cohort gap priority. Called automatically
     * whenever an employee completes a training so the freed slot is filled
     * with the next most urgent training.
     */
    public function fillForEmployee(Pegawai $pegawai): void
    {
        $group = Pegawai::where('jabatan', $pegawai->jabatan)
            ->where('jenjang', $pegawai->jenjang)
            ->get();

        $priorityOrder = $this->priorityOrderFor($group, $pegawai->jenjang);

        $this->assignSlots($pegawai, $priorityOrder, ['groups' => 0, 'assigned' => 0, 'skipped_full' => 0]);
    }

    /**
     * Build a priority-ordered collection of pelatihan for a cohort group.
     * Ordering rules:
     *  1. Training status priority: "Wajib" always ranks above "Opsional".
     *  2. Same status: trainings with the largest pool of uncompleted
     *     employees are ranked first to maximise class size.
     *
     * Eligibility mirrors the Grand Design rule seen by employees:
     *  - target_jabatan must contain the group's jabatan (JSON match), AND
     *  - jenjang must equal the group's jenjang OR be null/empty (general).
     *
     * @param  Collection<int, Pegawai>  $group
     * @return Collection<int, object{pelatihan: Pelatihan, uncompleted: int}>
     */
    protected function priorityOrderFor(Collection $group, string $jenjang): Collection
    {
        $groupId = $group->pluck('id');
        $jabatan = $group->first()?->jabatan;

        return Pelatihan::query()
            ->where(function ($query) use ($jabatan) {
                $query->whereJsonContains('target_jabatan', $jabatan);
            })
            ->where(function ($query) use ($jenjang) {
                $query->where('jenjang', $jenjang)
                    ->orWhereNull('jenjang')
                    ->orWhere('jenjang', '');
            })
            ->get()
            ->map(function (Pelatihan $pelatihan) use ($groupId, $group) {
                $completed = RiwayatPelatihan::query()
                    ->where('pelatihan_id', $pelatihan->id)
                    ->whereIn('pegawai_id', $groupId)
                    ->distinct()
                    ->count('pegawai_id');

                return (object) [
                    'pelatihan' => $pelatihan,
                    'uncompleted' => max(0, $group->count() - $completed),
                ];
            })
            ->filter(fn ($item) => $item->uncompleted > 0)
            ->sort(function (object $a, object $b) {
                $statusOrder = $this->statusPriority($a->pelatihan->status)
                    <=> $this->statusPriority($b->pelatihan->status);

                if ($statusOrder !== 0) {
                    return $statusOrder;
                }

                return $b->uncompleted <=> $a->uncompleted;
            })
            ->values();
    }

    /**
     * "Wajib" trainings must always rank ahead of "Opsional" ones.
     */
    protected function statusPriority(string $status): int
    {
        return $status === 'wajib' ? 0 : 1;
    }

    /**
     * @param  Collection<int, object{pelatihan: Pelatihan, uncompleted: int}>  $priorityOrder
     * @param  array{groups: int, assigned: int, skipped_full: int}  $stats
     * @return array{groups: int, assigned: int, skipped_full: int}
     */
    protected function assignSlots(Pegawai $pegawai, Collection $priorityOrder, array $stats): array
    {
        $completedIds = $this->completedTrainingIds($pegawai);
        $targetedIds = $this->activeTargetTrainingIds($pegawai);

        if (count($targetedIds) >= self::MAX_TARGETS) {
            $stats['skipped_full']++;

            return $stats;
        }

        $priority = 1;

        foreach ($priorityOrder as $item) {
            if ($priority > self::MAX_TARGETS) {
                break;
            }

            if ($completedIds->contains($item->pelatihan->id) || $targetedIds->contains($item->pelatihan->id)) {
                continue;
            }

            TargetPelatihan::create([
                'pegawai_id' => $pegawai->id,
                'pelatihan_id' => $item->pelatihan->id,
                'prioritas' => $priority,
                'status' => 'ditargetkan',
            ]);

            $targetedIds->push($item->pelatihan->id);
            $stats['assigned']++;

            $priority++;
        }

        return $stats;
    }

    /**
     * @return Collection<int, int>
     */
    protected function completedTrainingIds(Pegawai $pegawai): Collection
    {
        return $pegawai->riwayatPelatihan()
            ->whereNotNull('pelatihan_id')
            ->pluck('pelatihan_id');
    }

    /**
     * @return Collection<int, int>
     */
    protected function activeTargetTrainingIds(Pegawai $pegawai): Collection
    {
        return $pegawai->targetPelatihan()
            ->where('status', '!=', 'selesai')
            ->pluck('pelatihan_id');
    }

    protected function groupKey(Pegawai $pegawai): string
    {
        return $pegawai->jabatan.'|'.$pegawai->jenjang;
    }

    protected function extractJenjang(string $key): string
    {
        return explode('|', $key, 2)[1];
    }
}
