<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Pelatihan;
use App\Models\RiwayatPelatihan;
use Illuminate\Http\Request;

class RiwayatPelatihanService
{
    /**
     * Manually add a training record that was completed outside the
     * Grand Design (external training).
     */
    public function store(Pegawai $pegawai, Request $request): void
    {
        $validated = $request->validate([
            'pelatihan_id' => ['nullable', 'exists:pelatihan,id'],
            'judul_custom' => ['nullable', 'required_without:pelatihan_id', 'string', 'max:255'],
            'tahun' => ['required', 'string', 'max:4'],
            'penyelenggara' => ['nullable', 'string', 'max:255'],
            'file_sertifikat' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $file = $request->hasFile('file_sertifikat')
            ? $request->file('file_sertifikat')->store('sertifikat', 'local')
            : null;

        RiwayatPelatihan::create([
            'pegawai_id' => $pegawai->id,
            'pelatihan_id' => $validated['pelatihan_id'] ?? null,
            'judul_custom' => $validated['judul_custom'] ?? Pelatihan::find($validated['pelatihan_id'] ?? null)?->judul,
            'tahun' => $validated['tahun'],
            'penyelenggara' => $validated['penyelenggara'] ?? null,
            'file_sertifikat' => $file ?? null,
        ]);
    }
}