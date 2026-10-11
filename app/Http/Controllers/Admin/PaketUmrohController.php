<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaketUmrohRequest;
use App\Http\Requests\UpdatePaketUmrohRequest;
use App\Models\PaketUmroh;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaketUmrohController extends Controller
{
    public function index(): View
    {
        $pakets = PaketUmroh::orderBy('tanggal_keberangkatan')->paginate(15);

        return view('admin.pakets.index', compact('pakets'));
    }

    public function create(): View
    {
        return view('admin.pakets.create');
    }

    public function store(StorePaketUmrohRequest $request): RedirectResponse
    {
        $data = $this->normalizeData($request->validated());
        $paketUmroh = PaketUmroh::create($data);

        return redirect()
            ->route('admin.pakets.edit', $paketUmroh)
            ->with('success', 'Paket umroh berhasil ditambahkan.');
    }

    public function edit(PaketUmroh $paketUmroh): View
    {
        return view('admin.pakets.edit', compact('paketUmroh'));
    }

    public function update(UpdatePaketUmrohRequest $request, PaketUmroh $paketUmroh): RedirectResponse
    {
        $paketUmroh->update($this->normalizeData($request->validated()));

        return redirect()
            ->route('admin.pakets.edit', $paketUmroh)
            ->with('success', 'Data paket umroh berhasil diperbarui.');
    }

    public function updateStatus(Request $request, PaketUmroh $paketUmroh): RedirectResponse
    {
        $validated = $request->validate([
            'status_paket' => ['required', Rule::in(PaketUmroh::statuses())],
        ]);

        if (
            $validated['status_paket'] === PaketUmroh::STATUS_TERSEDIA
            && $paketUmroh->sisa_kuota < 1
        ) {
            return back()->withErrors([
                'status_paket' => 'Isi sisa kuota melalui menu edit sebelum mengubah status menjadi tersedia.',
            ]);
        }

        $paketUmroh->update($this->normalizeData($validated));

        return back()->with('success', 'Status paket umroh berhasil diperbarui.');
    }

    public function deactivate(PaketUmroh $paketUmroh): RedirectResponse
    {
        $paketUmroh->update(['status_paket' => PaketUmroh::STATUS_DITUTUP]);

        return back()->with('success', 'Paket umroh berhasil dinonaktifkan.');
    }

    private function normalizeData(array $data): array
    {
        if (($data['status_paket'] ?? null) === PaketUmroh::STATUS_PENUH) {
            $data['sisa_kuota'] = 0;
        }

        return $data;
    }
}
