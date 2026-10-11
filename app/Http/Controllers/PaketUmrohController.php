<?php

namespace App\Http\Controllers;

use App\Models\PaketUmroh;
use Illuminate\View\View;

class PaketUmrohController extends Controller
{
    public function index(): View
    {
        $pakets = PaketUmroh::tersedia()
            ->orderBy('tanggal_keberangkatan')
            ->paginate(9);

        return view('pakets.index', compact('pakets'));
    }

    public function show(PaketUmroh $paketUmroh): View
    {
        abort_unless($paketUmroh->isAvailable(), 404);

        return view('pakets.show', compact('paketUmroh'));
    }
}
