<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Pendaftaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Pendaftaran $pendaftaran): View
    {
        abort_unless($pendaftaran->user_id === Auth::id(), 403);
        $pendaftaran->ensurePaymentBills();
        $invoice = $pendaftaran->invoice;
        $this->loadInvoice($invoice);

        return view('invoice.show', compact('invoice'));
    }

    public function print(Invoice $invoice): View
    {
        abort_unless($invoice->pendaftaran->user_id === Auth::id(), 403);
        $invoice->pendaftaran->syncInvoice();
        $this->loadInvoice($invoice->refresh());

        return view('invoice.print', compact('invoice'));
    }

    private function loadInvoice(Invoice $invoice): void
    {
        $invoice->load([
            'pendaftaran.user',
            'pendaftaran.paketUmroh',
            'pendaftaran.jamaahs',
            'pendaftaran.pembayarans.verifier',
            'pendaftaran.cicilans.verifier',
        ]);
    }
}
