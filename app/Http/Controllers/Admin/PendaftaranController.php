<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use Illuminate\View\View;

class PendaftaranController extends Controller
{
    public function index(): View
    {
        $pendaftarans = Pendaftaran::with(['user', 'paketUmroh'])
            ->latest('tanggal_pendaftaran')
            ->paginate(15);

        return view('admin.pendaftaran.index', compact('pendaftarans'));
    }

    public function show(Pendaftaran $pendaftaran): View
    {
        $pendaftaran->load(['user', 'paketUmroh']);

        return view('admin.pendaftaran.show', compact('pendaftaran'));
    }
}
