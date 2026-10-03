<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\PelatihanImport;
use App\Models\Pelatihan;
use App\Support\GlobalFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class AdminPelatihanController extends Controller
{
    public function index(Request $request): Response
    {
        $jabatan = $request->string('jabatan')->toString();
        $jenjang = $request->string('jenjang')->toString();

        $pelatihan = GlobalFilters::applyToPelatihan(
            Pelatihan::query()
                ->when($request->filled('search'), function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('judul', 'like', "%{$search}%")
                            ->orWhere('kode_diklat', 'like', "%{$search}%")
                            ->orWhere('jenjang', 'like', "%{$search}%");
                    });
                }),
            $jabatan,
            $jenjang,
        )
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Pelatihan/Index', [
            'pelatihan' => $pelatihan,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'jabatan' => $jabatan,
                'jenjang' => $jenjang,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pelatihan/Create', [
            'kategoriOptions' => Pelatihan::KATEGORI_OPTIONS,
            'statusOptions' => Pelatihan::STATUS_OPTIONS,
            'targetJabatanOptions' => Pelatihan::TARGET_JABATAN_OPTIONS,
            'jenjangOptions' => Pelatihan::JENJANG_OPTIONS,
        ]);
    }

    public function edit(Pelatihan $pelatihan): Response
    {
        return Inertia::render('Admin/Pelatihan/Edit', [
            'pelatihan' => $pelatihan,
            'kategoriOptions' => Pelatihan::KATEGORI_OPTIONS,
            'statusOptions' => Pelatihan::STATUS_OPTIONS,
            'targetJabatanOptions' => Pelatihan::TARGET_JABATAN_OPTIONS,
            'jenjangOptions' => Pelatihan::JENJANG_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Pelatihan::create($validated);

        return redirect()
            ->route('admin.pelatihan.index')
            ->with('success', 'Master pelatihan berhasil ditambahkan.');
    }

    public function update(Request $request, Pelatihan $pelatihan): RedirectResponse
    {
        $validated = $request->validate($this->rules($pelatihan));

        $pelatihan->update($validated);

        return redirect()
            ->route('admin.pelatihan.index')
            ->with('success', 'Master pelatihan berhasil diperbarui.');
    }

    public function destroy(Pelatihan $pelatihan): RedirectResponse
    {
        $pelatihan->delete();

        return redirect()
            ->route('admin.pelatihan.index')
            ->with('success', 'Master pelatihan berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new PelatihanImport, $validated['file']);

        return back()->with('success', 'Master pelatihan berhasil diimpor dari Excel.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?Pelatihan $pelatihan = null): array
    {
        return [
            'kode_diklat' => ['required', 'string', 'max:255', Rule::unique('pelatihan', 'kode_diklat')->ignore($pelatihan?->id)],
            'judul' => ['required', 'string', 'max:255'],
            'jenjang' => ['nullable', Rule::in(Pelatihan::JENJANG_OPTIONS)],
            'subsektor' => ['required', 'string', 'max:255'],
            'target_jabatan' => ['required', 'array', 'min:1'],
            'target_jabatan.*' => ['required', Rule::in(Pelatihan::TARGET_JABATAN_OPTIONS)],
            'kategori' => ['required', Rule::in(Pelatihan::KATEGORI_OPTIONS)],
            'status' => ['required', Rule::in(Pelatihan::STATUS_OPTIONS)],
        ];
    }
}