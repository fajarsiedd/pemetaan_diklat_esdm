<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Pelatihan;
use App\Models\RiwayatPelatihan;
use App\Models\TargetPelatihan;
use App\Services\TargetPelatihanService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $totalPegawai = Pegawai::count();
        $totalPelatihan = Pelatihan::count();
        $totalTarget = TargetPelatihan::where('status', '!=', 'selesai')->count();
        $totalRiwayat = RiwayatPelatihan::count();

        $completionRate = ($totalTarget + $totalRiwayat) > 0
            ? (int) round(($totalRiwayat / ($totalTarget + $totalRiwayat)) * 100)
            : 0;

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => [
                'total_pegawai' => $totalPegawai,
                'total_pelatihan' => $totalPelatihan,
                'total_target' => $totalTarget,
                'total_riwayat' => $totalRiwayat,
                'completion_rate' => $completionRate,
            ],
            'charts' => [
                'employeeDistribution' => $this->employeeDistribution(),
                'trainingCategories' => $this->trainingCategories(),
                'trainingStatusRatio' => $this->trainingStatusRatio(),
                'topTargetedTrainings' => $this->topTargetedTrainings(),
            ],
        ]);
    }

    /**
     * Grouped counts of employees per jabatan (categories) × jenjang (series).
     *
     * @return array{categories: array<int, string>, series: array<int, array{name: string, data: array<int, int>}>}
     */
    protected function employeeDistribution(): array
    {
        $jabatan = Pegawai::query()->distinct()->orderBy('jabatan')->pluck('jabatan');
        $jenjang = Pegawai::query()->distinct()->orderBy('jenjang')->pluck('jenjang');

        $counts = Pegawai::query()
            ->selectRaw('jabatan, jenjang, COUNT(*) as total')
            ->groupBy('jabatan', 'jenjang')
            ->get()
            ->keyBy(fn ($row) => $row->jabatan.'|'.$row->jenjang)
            ->mapWithKeys(fn ($row, $key) => [$key => (int) $row->total]);

        $series = $jenjang->map(fn (string $level) => [
            'name' => $level,
            'data' => $jabatan->map(fn (string $position) => $counts[$position.'|'.$level] ?? 0)->all(),
        ])->values()->all();

        return [
            'categories' => $jabatan->values()->all(),
            'series' => $series,
        ];
    }

    /**
     * Count of training programs per kategori, in a fixed display order.
     *
     * @return array{labels: array<int, string>, series: array<int, int>}
     */
    protected function trainingCategories(): array
    {
        $labels = [
            'technical' => 'Technical',
            'legal' => 'Legal',
            'commercial' => 'Commercial',
            'soft_skill' => 'Soft Skill',
        ];

        $counts = Pelatihan::query()
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
    protected function trainingStatusRatio(): array
    {
        $counts = Pelatihan::query()
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
    protected function topTargetedTrainings(): array
    {
        return Pelatihan::query()
            ->withCount(['targetPelatihan as assigned_count' => function ($query) {
                $query->where('status', '!=', 'selesai');
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