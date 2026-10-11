<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJamaahRequest;
use App\Http\Requests\UpdateJamaahRequest;
use App\Models\Jamaah;
use App\Models\Pendaftaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JamaahController extends Controller
{
    public function index(Pendaftaran $pendaftaran): View
    {
        $this->authorizeRegistration($pendaftaran);
        $pendaftaran->load(['paketUmroh', 'jamaahs']);

        return view('jamaah.index', compact('pendaftaran'));
    }

    public function create(Pendaftaran $pendaftaran): View
    {
        $this->authorizeRegistration($pendaftaran);
        abort_if($pendaftaran->jamaahs()->count() >= $pendaftaran->jumlah_jamaah, 422, 'Jumlah data jamaah sudah memenuhi batas pendaftaran.');

        return view('jamaah.create', compact('pendaftaran'));
    }

    public function store(StoreJamaahRequest $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorizeRegistration($pendaftaran);

        $jamaah = DB::transaction(function () use ($request, $pendaftaran): Jamaah {
            $registration = Pendaftaran::lockForUpdate()->findOrFail($pendaftaran->id);

            if ($registration->jamaahs()->count() >= $registration->jumlah_jamaah) {
                abort(422, 'Jumlah data jamaah sudah memenuhi batas pendaftaran.');
            }

            return $registration->jamaahs()->create($request->validated());
        });

        return redirect()->route('jamaah.show', $jamaah)->with('success', 'Data jamaah berhasil ditambahkan.');
    }

    public function show(Jamaah $jamaah): View
    {
        $this->authorizeJamaah($jamaah);
        $jamaah->load('pendaftaran.paketUmroh');

        return view('jamaah.show', compact('jamaah'));
    }

    public function edit(Jamaah $jamaah): View
    {
        $this->authorizeJamaah($jamaah);

        return view('jamaah.edit', compact('jamaah'));
    }

    public function update(UpdateJamaahRequest $request, Jamaah $jamaah): RedirectResponse
    {
        $this->authorizeJamaah($jamaah);
        $jamaah->update($request->validated());

        return redirect()->route('jamaah.show', $jamaah)->with('success', 'Data jamaah berhasil diperbarui.');
    }

    public function destroy(Jamaah $jamaah): RedirectResponse
    {
        $this->authorizeJamaah($jamaah);
        abort_unless($jamaah->pendaftaran->canDeleteJamaah(), 422, 'Data jamaah tidak dapat dihapus setelah masuk pembayaran atau verifikasi.');

        $pendaftaran = $jamaah->pendaftaran;
        $jamaah->delete();

        return redirect()->route('pendaftaran.jamaah.index', $pendaftaran)->with('success', 'Data jamaah berhasil dihapus.');
    }

    private function authorizeRegistration(Pendaftaran $pendaftaran): void
    {
        abort_unless($pendaftaran->user_id === Auth::id(), 403);
    }

    private function authorizeJamaah(Jamaah $jamaah): void
    {
        abort_unless($jamaah->pendaftaran->user_id === Auth::id(), 403);
    }
}
