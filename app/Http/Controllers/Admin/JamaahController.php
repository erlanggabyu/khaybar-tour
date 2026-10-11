<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jamaah;
use App\Models\Pendaftaran;
use Illuminate\View\View;

class JamaahController extends Controller
{
    public function index(Pendaftaran $pendaftaran): View
    {
        $pendaftaran->load(['user', 'paketUmroh', 'jamaahs']);

        return view('admin.jamaah.index', compact('pendaftaran'));
    }

    public function show(Jamaah $jamaah): View
    {
        $jamaah->load('pendaftaran.user', 'pendaftaran.paketUmroh');

        return view('admin.jamaah.show', compact('jamaah'));
    }
}
