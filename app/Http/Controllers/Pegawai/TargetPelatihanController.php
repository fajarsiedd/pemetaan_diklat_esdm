<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Services\TargetPelatihanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TargetPelatihanController extends Controller
{
    public function complete(Request $request, TargetPelatihan $target): RedirectResponse
    {
        abort_unless(
            $target->pegawai_id === Auth::user()->pegawai?->id,
            403,
        );

        $validated = $request->validate([
            'status' => ['required', Rule::in(['sedang_proses', 'selesai'])],
        ]);

        app(TargetPelatihanService::class)->complete($target, $validated['status']);

        return back()->with(
            'success',
            $validated['status'] === 'selesai'
                ? 'Target ditandai selesai dan dipindahkan ke riwayat.'
                : 'Status target diperbarui menjadi sedang proses.',
        );
    }
}
