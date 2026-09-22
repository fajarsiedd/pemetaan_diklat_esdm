<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Pelatihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PegawaiProfileController extends Controller
{
    public function show(): Response
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        return Inertia::render('Pegawai/Profile/Index', [
            'pegawai' => $pegawai ? array_merge($pegawai->toArray(), [
                'email' => $user->email,
            ]) : null,
            'targets' => $pegawai
                ? $pegawai->targetPelatihan()
                    ->where('status', '!=', 'selesai')
                    ->with('pelatihan')
                    ->orderBy('prioritas')
                    ->get()
                : [],
            'riwayat' => $pegawai
                ? $pegawai->riwayatPelatihan()
                    ->with('pelatihan')
                    ->latest('id')
                    ->get()
                : [],
            'grandDesign' => $pegawai
                ? Pelatihan::query()
                    ->when($pegawai->jabatan, function ($query) use ($pegawai) {
                        $query->whereJsonContains('target_jabatan', $pegawai->jabatan);
                    })
                    ->where(function ($query) use ($pegawai) {
                        $query->where('jenjang', $pegawai->jenjang)
                            ->orWhereNull('jenjang')
                            ->orWhere('jenjang', '');
                    })
                    ->get()
                : [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:255', Rule::unique('pegawai', 'nip')->ignore($user->pegawai?->id)],
            'jabatan' => ['required', Rule::in(['Inspektur Ketenagalistrikan', 'Penyelidik Bumi'])],
            'jenjang' => ['required', Rule::in(Pegawai::JENJANG_OPTIONS)],
            'unit_kerja' => ['required', 'string', 'max:255'],
            'provinsi' => ['required', 'string', 'max:255'],
        ]);

        $pegawai = $user->pegawai;

        if ($pegawai) {
            $pegawai->update($validated);
        } else {
            Pegawai::create(['user_id' => $user->id, ...$validated]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
