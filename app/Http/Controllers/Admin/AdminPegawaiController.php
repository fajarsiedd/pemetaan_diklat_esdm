<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Pelatihan;
use App\Models\RiwayatPelatihan;
use App\Models\TargetPelatihan;
use App\Models\User;
use App\Services\RiwayatPelatihanService;
use App\Services\TargetPelatihanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminPegawaiController extends Controller
{
    public function index(Request $request): Response
    {
        $pegawai = Pegawai::query()
            ->with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            })
            ->withCount(['targetPelatihan as target_count' => function ($query) {
                $query->where('status', '!=', 'selesai');
            }, 'riwayatPelatihan as riwayat_count'])
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Pegawai/Index', [
            'pegawai' => $pegawai,
            'filters' => ['search' => $request->string('search')->toString()],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pegawai/Create', [
            'jabatanOptions' => Pelatihan::TARGET_JABATAN_OPTIONS,
            'jenjangOptions' => Pegawai::JENJANG_OPTIONS,
        ]);
    }

    public function edit(Pegawai $pegawai): Response
    {
        $pegawai->load('user');

        return Inertia::render('Admin/Pegawai/Edit', [
            'pegawai' => array_merge($pegawai->toArray(), [
                'email' => $pegawai->user?->email,
            ]),
            'jabatanOptions' => Pelatihan::TARGET_JABATAN_OPTIONS,
            'jenjangOptions' => Pegawai::JENJANG_OPTIONS,
            'targets' => $pegawai->targetPelatihan()
                ->where('status', '!=', 'selesai')
                ->with('pelatihan')
                ->orderBy('prioritas')
                ->get(),
            'riwayat' => $pegawai->riwayatPelatihan()
                ->with('pelatihan')
                ->latest('id')
                ->get(),
            'grandDesign' => Pelatihan::query()
                ->when($pegawai->jabatan, function ($query) use ($pegawai) {
                    $query->whereJsonContains('target_jabatan', $pegawai->jabatan);
                })
                ->where(function ($query) use ($pegawai) {
                    $query->where('jenjang', $pegawai->jenjang)
                        ->orWhereNull('jenjang')
                        ->orWhere('jenjang', '');
                })
                ->get(),
        ]);
    }

    public function completeTarget(Request $request, Pegawai $pegawai, TargetPelatihan $target): RedirectResponse
    {
        abort_unless($target->pegawai_id === $pegawai->id, 403);

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

    public function storeRiwayat(Request $request, Pegawai $pegawai): RedirectResponse
    {
        app(RiwayatPelatihanService::class)->store($pegawai, $request);

        return back()->with('success', 'Riwayat pelatihan berhasil ditambahkan.');
    }

    public function destroyRiwayat(Pegawai $pegawai, RiwayatPelatihan $riwayat): RedirectResponse
    {
        abort_unless($riwayat->pegawai_id === $pegawai->id, 403);

        if ($riwayat->file_sertifikat) {
            Storage::disk('local')->delete($riwayat->file_sertifikat);
        }

        $riwayat->delete();

        return back()->with('success', 'Riwayat pelatihan berhasil dihapus.');
    }

    public function downloadRiwayat(Pegawai $pegawai, RiwayatPelatihan $riwayat)
    {
        abort_unless($riwayat->pegawai_id === $pegawai->id, 404);
        abort_unless($riwayat->file_sertifikat, 404);

        return Storage::disk('local')->download(
            $riwayat->file_sertifikat,
            'sertifikat_'.str_replace(' ', '_', $riwayat->judul ?? 'pelatihan').'.'.pathinfo($riwayat->file_sertifikat, PATHINFO_EXTENSION),
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'nip' => ['required', 'string', 'max:255', 'unique:pegawai,nip'],
            'jabatan' => ['required', Rule::in(Pelatihan::TARGET_JABATAN_OPTIONS)],
            'jenjang' => ['required', Rule::in(Pegawai::JENJANG_OPTIONS)],
            'unit_kerja' => ['required', 'string', 'max:255'],
            'provinsi' => ['required', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'password' => 'password',
            'role' => 'pegawai',
        ]);

        Pegawai::create([
            'user_id' => $user->id,
            'nama' => $validated['nama'],
            'nip' => $validated['nip'],
            'jabatan' => $validated['jabatan'],
            'jenjang' => $validated['jenjang'],
            'unit_kerja' => $validated['unit_kerja'],
            'provinsi' => $validated['provinsi'],
        ]);

        return redirect()
            ->route('admin.pegawai.index')
            ->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function update(Request $request, Pegawai $pegawai): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pegawai->user_id)],
            'nip' => ['required', 'string', 'max:255', Rule::unique('pegawai', 'nip')->ignore($pegawai->id)],
            'jabatan' => ['required', Rule::in(Pelatihan::TARGET_JABATAN_OPTIONS)],
            'jenjang' => ['required', Rule::in(Pegawai::JENJANG_OPTIONS)],
            'unit_kerja' => ['required', 'string', 'max:255'],
            'provinsi' => ['required', 'string', 'max:255'],
        ]);

        $pegawai->user()->update([
            'name' => $validated['nama'],
            'email' => $validated['email'],
        ]);

        $pegawai->update(array_diff_key($validated, array_flip(['email'])));

        return redirect()
            ->route('admin.pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai): RedirectResponse
    {
        $pegawai->user()->delete();

        return redirect()
            ->route('admin.pegawai.index')
            ->with('success', 'Pegawai berhasil dihapus.');
    }
}