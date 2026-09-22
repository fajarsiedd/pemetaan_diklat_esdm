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
        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => [
                'total_pegawai' => Pegawai::count(),
                'total_pelatihan' => Pelatihan::count(),
                'total_target' => TargetPelatihan::where('status', '!=', 'selesai')->count(),
                'total_riwayat' => RiwayatPelatihan::count(),
            ],
        ]);
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