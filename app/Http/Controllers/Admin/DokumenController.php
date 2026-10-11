<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyDokumenRequest;
use App\Models\Dokumen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DokumenController extends Controller
{
    public function index(): View
    {
        $dokumens = Dokumen::with(['jamaah.pendaftaran.user', 'verifier'])
            ->orderByRaw("status_dokumen = 'Menunggu Pemeriksaan' DESC")
            ->latest()
            ->paginate(20);

        return view('admin.dokumen.index', compact('dokumens'));
    }

    public function show(Dokumen $dokumen): View
    {
        $dokumen->load(['jamaah.pendaftaran.user', 'jamaah.pendaftaran.paketUmroh', 'verifier']);

        return view('admin.dokumen.show', compact('dokumen'));
    }

    public function verify(VerifyDokumenRequest $request, Dokumen $dokumen): RedirectResponse
    {
        $validated = $request->validated();
        $dokumen->update([
            ...$validated,
            'catatan_admin' => $validated['status_dokumen'] === Dokumen::STATUS_VALID ? null : $validated['catatan_admin'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Status dokumen berhasil diperbarui.');
    }
}
