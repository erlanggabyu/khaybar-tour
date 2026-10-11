<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePendaftaranRequest;
use App\Models\PaketUmroh;
use App\Models\Pendaftaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PendaftaranController extends Controller
{
    public function index(): View
    {
        $pendaftarans = Pendaftaran::with('paketUmroh')
            ->where('user_id', Auth::id())
            ->latest('tanggal_pendaftaran')
            ->paginate(10);

        return view('pendaftaran.index', compact('pendaftarans'));
    }

    public function create(PaketUmroh $paketUmroh): View
    {
        abort_unless($paketUmroh->isAvailable(), 404);

        return view('pendaftaran.create', compact('paketUmroh'));
    }

    public function store(StorePendaftaranRequest $request, PaketUmroh $paketUmroh): RedirectResponse
    {
        $pendaftaran = DB::transaction(function () use ($request, $paketUmroh): Pendaftaran {
            $paket = PaketUmroh::lockForUpdate()->findOrFail($paketUmroh->id);
            $jumlah = (int) $request->validated('jumlah_jamaah');

            if (! $paket->isAvailable() || $jumlah > $paket->sisa_kuota) {
                abort(422, 'Paket tidak tersedia atau sisa kuota tidak mencukupi.');
            }

            $pendaftaran = Pendaftaran::create([
                'kode_pendaftaran' => $this->generateCode(),
                'user_id' => Auth::id(),
                'paket_umroh_id' => $paket->id,
                'tanggal_pendaftaran' => now()->toDateString(),
                'jumlah_jamaah' => $jumlah,
                'total_harga' => $paket->harga * $jumlah,
                'catatan_pelanggan' => $request->validated('catatan_pelanggan'),
                'status_pendaftaran' => Pendaftaran::STATUS_MENUNGGU_DP,
            ]);
            $pendaftaran->ensurePaymentBills();

            $sisaKuota = $paket->sisa_kuota - $jumlah;
            $paket->update([
                'sisa_kuota' => $sisaKuota,
                'status_paket' => $sisaKuota === 0 ? PaketUmroh::STATUS_PENUH : $paket->status_paket,
            ]);

            return $pendaftaran;
        });

        return redirect()
            ->route('pendaftaran.show', $pendaftaran)
            ->with('success', 'Pendaftaran berhasil disimpan. Silakan lanjutkan pembayaran DP pada tahap berikutnya.');
    }

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorizeOwner($pendaftaran);
        $pendaftaran->load('paketUmroh');

        return view('pendaftaran.show', compact('pendaftaran'));
    }

    public function cancel(Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorizeOwner($pendaftaran);

        DB::transaction(function () use ($pendaftaran): void {
            $registration = Pendaftaran::lockForUpdate()->findOrFail($pendaftaran->id);

            if (! $registration->canBeCancelled()) {
                abort(422, 'Pendaftaran ini tidak dapat dibatalkan.');
            }

            $paket = PaketUmroh::lockForUpdate()->findOrFail($registration->paket_umroh_id);
            $registration->update(['status_pendaftaran' => Pendaftaran::STATUS_DIBATALKAN]);
            $registration->syncInvoice();

            $data = [
                'sisa_kuota' => min($paket->kuota_jamaah, $paket->sisa_kuota + $registration->jumlah_jamaah),
            ];
            if ($paket->status_paket === PaketUmroh::STATUS_PENUH) {
                $data['status_paket'] = PaketUmroh::STATUS_TERSEDIA;
            }
            $paket->update($data);
        });

        return back()->with('success', 'Pendaftaran berhasil dibatalkan.');
    }

    private function authorizeOwner(Pendaftaran $pendaftaran): void
    {
        abort_unless($pendaftaran->user_id === Auth::id(), 403);
    }

    private function generateCode(): string
    {
        do {
            $code = 'DFT-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Pendaftaran::where('kode_pendaftaran', $code)->exists());

        return $code;
    }
}
