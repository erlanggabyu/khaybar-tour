<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyCicilanRequest;
use App\Models\Cicilan;
use App\Models\Invoice;
use App\Models\Pendaftaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CicilanController extends Controller
{
    public function show(Cicilan $cicilan): View
    {
        $cicilan->load(['pendaftaran.user', 'pendaftaran.paketUmroh', 'verifier']);

        return view('admin.cicilan.show', compact('cicilan'));
    }

    public function verify(VerifyCicilanRequest $request, Cicilan $cicilan): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $cicilan): void {
            $installment = Cicilan::lockForUpdate()->findOrFail($cicilan->id);
            abort_unless($installment->status === Cicilan::STATUS_MENUNGGU, 422, 'Cicilan ini tidak sedang menunggu verifikasi.');

            $isValid = $validated['status'] === Cicilan::STATUS_VALID;
            $installment->update([
                'status' => $validated['status'],
                'catatan_admin' => $isValid ? null : $validated['catatan_admin'],
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'paid_at' => $isValid ? now() : null,
            ]);

            $registration = $installment->pendaftaran()->lockForUpdate()->firstOrFail();
            $registration->refresh();
            $invoice = $registration->syncInvoice();
            $allInstallmentsValid = $registration->cicilans()->where('status', '!=', Cicilan::STATUS_VALID)->doesntExist();

            $registration->update([
                'status_pendaftaran' => $invoice->status_invoice === Invoice::STATUS_LUNAS || $allInstallmentsValid
                    ? Pendaftaran::STATUS_LUNAS
                    : Pendaftaran::STATUS_DP_TERVERIFIKASI,
            ]);
            $registration->syncInvoice();
        });

        return back()->with('success', 'Status cicilan berhasil diperbarui.');
    }
}
