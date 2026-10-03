<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Pelatihan;
use App\Models\RiwayatPelatihan;
use App\Models\TargetPelatihan;
use App\Services\TargetPelatihanService;
use App\Support\GlobalFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $jabatan = $request->string('jabatan')->toString();
        $jenjang = $request->string('jenjang')->toString();

        $filtersActive = GlobalFilters::active($jabatan) || GlobalFilters::active($jenjang);

        $totalPegawai = GlobalFilters::applyToPegawai(Pegawai::query(), $jabatan, $jenjang)->count();

        $totalPelatihan = GlobalFilters::applyToPelatihan(Pelatihan::query(), $jabatan, $jenjang)->count();

        $totalTarget = TargetPelatihan::query()
            ->where('status', '!=', 'selesai')
            ->when($filtersActive, function (Builder $query) use ($jabatan, $jenjang) {
                $query->whereHas('pegawai', fn (Builder $q) => GlobalFilters::applyToPegawai($q, $jabatan, $jenjang));
            })
            ->count();

        $totalRiwayat = RiwayatPelatihan::query()
            ->when($filtersActive, function (Builder $query) use ($jabatan, $jenjang) {
                $query->whereHas('pegawai', fn (Builder $q) => GlobalFilters::applyToPegawai($q, $jabatan, $jenjang));
            })
            ->count();

        $completionRate = ($totalTarget + $totalRiwayat) > 0
            ? (int) round(($totalRiwayat / ($totalTarget + $totalRiwayat)) * 100)
            : 0;

        return Inertia::render('Admin/Dashboard/Index', [
            'filters' => [
                'jabatan' => $jabatan,
                'jenjang' => $jenjang,
            ],
            'stats' => [
                'total_pegawai' => $totalPegawai,
                'total_pelatihan' => $totalPelatihan,
                'total_target' => $totalTarget,
                'total_riwayat' => $totalRiwayat,
                'completion_rate' => $completionRate,
            ],
            'charts' => [
                'employeeDistribution' => $this->employeeDistribution($jabatan, $jenjang),
                'trainingCategories' => $this->trainingCategories($jabatan, $jenjang),
                'trainingStatusRatio' => $this->trainingStatusRatio($jabatan, $jenjang),
                'topTargetedTrainings' => $this->topTargetedTrainings($jabatan, $jenjang),
            ],
        ]);
    }

    /**
     * Grouped counts of employees per jabatan (categories) × jenjang (series).
     *
     * @return array{categories: array<int, string>, series: array<int, array{name: string, data: array<int, int>}>}
     */
    protected function employeeDistribution(?string $jabatan, ?string $jenjang): array
    {
        $base = GlobalFilters::applyToPegawai(Pegawai::query(), $jabatan, $jenjang);

        $positions = (clone $base)->distinct()->orderBy('jabatan')->pluck('jabatan');
        $levels = (clone $base)->distinct()->orderBy('jenjang')->pluck('jenjang');

        $counts = (clone $base)
            ->selectRaw('jabatan, jenjang, COUNT(*) as total')
            ->groupBy('jabatan', 'jenjang')
            ->get()
            ->keyBy(fn ($row) => $row->jabatan.'|'.$row->jenjang)
            ->mapWithKeys(fn ($row, $key) => [$key => (int) $row->total]);

        $series = $levels->map(fn (string $level) => [
            'name' => $level,
            'data' => $positions->map(fn (string $position) => $counts[$position.'|'.$level] ?? 0)->all(),
        ])->values()->all();

        return [
            'categories' => $positions->values()->all(),
            'series' => $series,
        ];
    }

    /**
     * Count of training programs per kategori, in a fixed display order.
     *
     * @return array{labels: array<int, string>, series: array<int, int>}
     */
    protected function trainingCategories(?string $jabatan, ?string $jenjang): array
    {
        $labels = [
            'technical' => 'Technical',
            'legal' => 'Legal',
            'commercial' => 'Commercial',
            'soft_skill' => 'Soft Skill',
        ];

        $counts = GlobalFilters::applyToPelatihan(Pelatihan::query(), $jabatan, $jenjang)
            ->selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori')
            ->map(fn ($total) => (int) $total);

        $series = [];
        foreach ($labels as $key => $label) {
            $series[] = $counts[$key] ?? 0;
        }

        return [
            'labels' => array_values($labels),
            'series' => $series,
        ];
    }

    /**
     * Count of training programs per status (Wajib vs Opsional).
     *
     * @return array{labels: array<int, string>, series: array<int, int>}
     */
    protected function trainingStatusRatio(?string $jabatan, ?string $jenjang): array
    {
        $counts = GlobalFilters::applyToPelatihan(Pelatihan::query(), $jabatan, $jenjang)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total);

        return [
            'labels' => ['Wajib', 'Opsional'],
            'series' => [$counts['wajib'] ?? 0, $counts['opsional'] ?? 0],
        ];
    }

    /**
     * Top 5 training programs by active assigned targets.
     *
     * @return array<int, array{judul: string, kode_diklat: string, kategori: string, count: int}>
     */
    protected function topTargetedTrainings(?string $jabatan, ?string $jenjang): array
    {
        return Pelatihan::query()
            ->withCount(['targetPelatihan as assigned_count' => function ($query) use ($jabatan, $jenjang) {
                $query->where('status', '!=', 'selesai');
                $query->when(GlobalFilters::active($jabatan) || GlobalFilters::active($jenjang), function (Builder $q) use ($jabatan, $jenjang) {
                    $q->whereHas('pegawai', fn (Builder $w) => GlobalFilters::applyToPegawai($w, $jabatan, $jenjang));
                });
            }])
            ->orderByDesc('assigned_count')
            ->limit(5)
            ->get(['id', 'kode_diklat', 'judul', 'kategori'])
            ->map(fn (Pelatihan $pelatihan) => [
                'judul' => $pelatihan->judul,
                'kode_diklat' => $pelatihan->kode_diklat,
                'kategori' => $pelatihan->kategori,
                'count' => (int) $pelatihan->assigned_count,
            ])
            ->filter(fn (array $item) => $item['count'] > 0)
            ->values()
            ->all();
    }

    public function recalculate(TargetPelatihanService $service): RedirectResponse
    {
        try {
            $stats = $service->recalculate();

            return back()->with('success', sprintf(
                'Target pelatihan dihitung ulang: %d kelompok, %d target ditugaskan, %d pegawai penuh (%d slot/pegawai).',
                $stats['groups'],
                $stats['assigned'],
                $stats['skipped_full'],
                TargetPelatihanService::MAX_TARGETS,
            ));
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal menghitung ulang target pelatihan. Silakan coba lagi.');
        }
    }
}