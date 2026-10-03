<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelatihan;
use App\Support\GlobalFilters;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminTargetPelatihanController extends Controller
{
    public function index(Request $request): Response
    {
        $jabatan = $request->string('jabatan')->toString();
        $jenjang = $request->string('jenjang')->toString();

        $pelatihan = GlobalFilters::applyToTrainingTargets(
            Pelatihan::query()
                ->withCount(['targetPelatihan as target_pelatihan_count' => function ($query) {
                    $query->where('status', '!=', 'selesai');
                }])
                ->with(['targetPelatihan.pegawai' => function ($query) {
                    $query->select(['id', 'nama', 'nip', 'jabatan', 'jenjang', 'unit_kerja']);
                }])
                ->having('target_pelatihan_count', '>', 0)
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = $request->string('search')->toString();

                    $query->where(function ($query) use ($search) {
                        $query->where('judul', 'like', "%{$search}%")
                            ->orWhere('kode_diklat', 'like', "%{$search}%");
                    });
                })
                ->when($request->filled('kategori'), function ($query) use ($request) {
                    $query->where('kategori', $request->string('kategori')->toString());
                })
                ->when($request->filled('status'), function ($query) use ($request) {
                    $query->where('status', $request->string('status')->toString());
                }),
            $jabatan,
            $jenjang,
        )
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/TargetPelatihan/Index', [
            'pelatihan' => $pelatihan,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'kategori' => $request->string('kategori')->toString(),
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }
}