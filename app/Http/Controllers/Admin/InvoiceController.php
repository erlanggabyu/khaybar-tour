<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Invoice $invoice): View
    {
        $invoice->pendaftaran->syncInvoice();
        $invoice->refresh()->load([
            'pendaftaran.user',
            'pendaftaran.paketUmroh',
            'pendaftaran.jamaahs',
            'pendaftaran.pembayarans.verifier',
        ]);

        return view('invoice.print', compact('invoice'));
    }
}
