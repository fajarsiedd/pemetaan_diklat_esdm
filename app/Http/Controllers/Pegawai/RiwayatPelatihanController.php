<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPelatihan;
use App\Services\RiwayatPelatihanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RiwayatPelatihanController extends Controller
{
    /**
     * Manually add a training record that was completed outside the
     * Grand Design (external training).
     */
    public function store(Request $request): RedirectResponse
    {
        $pegawai = Auth::user()->pegawai;

        abort_unless($pegawai, 403, 'Profil pegawai belum lengkap.');

        app(RiwayatPelatihanService::class)->store($pegawai, $request);

        return back()->with('success', 'Riwayat pelatihan berhasil ditambahkan.');
    }

    public function destroy(RiwayatPelatihan $riwayat): RedirectResponse
    {
        abort_unless($riwayat->pegawai_id === Auth::user()->pegawai?->id, 403);

        if ($riwayat->file_sertifikat) {
            Storage::disk('local')->delete($riwayat->file_sertifikat);
        }

        $riwayat->delete();

        return back()->with('success', 'Riwayat pelatihan berhasil dihapus.');
    }

    public function download(RiwayatPelatihan $riwayat)
    {
        abort_unless($riwayat->file_sertifikat, 404);

        abort_unless(
            Auth::user()->isAdmin() || $riwayat->pegawai_id === Auth::user()->pegawai?->id,
            403,
        );

        return Storage::disk('local')->download(
            $riwayat->file_sertifikat,
            'sertifikat_'.str_replace(' ', '_', $riwayat->judul ?? 'pelatihan').'.'.pathinfo($riwayat->file_sertifikat, PATHINFO_EXTENSION),
        );
    }
}
