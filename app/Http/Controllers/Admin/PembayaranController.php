<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyPembayaranRequest;
use App\Models\Cicilan;
use App\Models\Invoice;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function index(): View
    {
        $pembayarans = Pembayaran::with(['pendaftaran.user', 'pendaftaran.paketUmroh', 'verifier'])
            ->orderByRaw("status_pembayaran = 'Menunggu Verifikasi' DESC")
            ->latest()
            ->paginate(20);

        $cicilans = Cicilan::with(['pendaftaran.user', 'pendaftaran.paketUmroh', 'verifier'])
            ->orderByRaw("status = 'Menunggu Verifikasi' DESC")
            ->latest()
            ->paginate(20, ['*'], 'cicilan_page');

        return view('admin.pembayaran.index', compact('pembayarans', 'cicilans'));
    }

    public function show(Pembayaran $pembayaran): View
    {
        $pembayaran->load(['pendaftaran.user', 'pendaftaran.paketUmroh', 'verifier']);

        return view('admin.pembayaran.show', compact('pembayaran'));
    }

    public function verify(VerifyPembayaranRequest $request, Pembayaran $pembayaran): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $pembayaran): void {
            $payment = Pembayaran::lockForUpdate()->findOrFail($pembayaran->id);
            abort_unless($payment->status_pembayaran === Pembayaran::STATUS_MENUNGGU, 422, 'Pembayaran ini tidak sedang menunggu verifikasi.');

            $isValid = $validated['status_pembayaran'] === Pembayaran::STATUS_VALID;
            if ($payment->jenis_pembayaran === Pembayaran::JENIS_PELUNASAN && $isValid) {
                $dpIsValid = $payment->pendaftaran->pembayarans()
                    ->where('jenis_pembayaran', Pembayaran::JENIS_DP)
                    ->where('status_pembayaran', Pembayaran::STATUS_VALID)
                    ->exists();
                abort_unless($dpIsValid, 422, 'Pelunasan tidak dapat divalidasi sebelum pembayaran DP Valid.');

                $installmentStarted = $payment->pendaftaran->cicilans()
                    ->where(function ($query): void {
                        $query->where('status', '!=', Cicilan::STATUS_BELUM_BAYAR)
                            ->orWhereNotNull('bukti_file');
                    })
                    ->exists();
                abort_unless(! $installmentStarted, 422, 'Pelunasan langsung tidak dapat divalidasi karena cicilan sudah dimulai.');
            }

            $payment->update([
                'status_pembayaran' => $validated['status_pembayaran'],
                'catatan_admin' => $isValid ? null : $validated['catatan_admin'],
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            $registration = $payment->pendaftaran()->lockForUpdate()->firstOrFail();
            $invoice = $registration->syncInvoice();
            $status = match (true) {
                $payment->jenis_pembayaran === Pembayaran::JENIS_DP && $isValid => Pendaftaran::STATUS_DP_TERVERIFIKASI,
                $payment->jenis_pembayaran === Pembayaran::JENIS_PELUNASAN && $isValid => Pendaftaran::STATUS_LUNAS,
                $payment->jenis_pembayaran === Pembayaran::JENIS_DP && ! $isValid => Pendaftaran::STATUS_MENUNGGU_DP,
                $invoice->status_invoice === Invoice::STATUS_LUNAS => Pendaftaran::STATUS_LUNAS,
                default => Pendaftaran::STATUS_DP_TERVERIFIKASI,
            };
            $registration->update(['status_pendaftaran' => $status]);
            $registration->syncInvoice();
        });

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }
}
