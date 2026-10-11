<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\PaketUmroh;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Entry point to redirect users based on their role.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isPelanggan()) {
            return redirect()->route('pelanggan.dashboard');
        }

        abort(403, 'Akses ditolak. Peran tidak dikenali.');
    }

    /**
     * Render the admin dashboard view.
     */
    public function adminDashboard()
    {
        $totalPakets = PaketUmroh::count();
        $totalPendaftarans = Pendaftaran::count();
        $pendingPayments = Pembayaran::where('status_pembayaran', 'Menunggu Verifikasi')->count();
        $pendingDocuments = Dokumen::where('status_dokumen', 'Menunggu Pemeriksaan')
            ->distinct('pendaftaran_id')
            ->count('pendaftaran_id');

        $recentPendaftarans = Pendaftaran::with(['user', 'paketUmroh'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalPakets',
            'totalPendaftarans',
            'pendingPayments',
            'pendingDocuments',
            'recentPendaftarans'
        ));
    }

    /**
     * Render the customer dashboard view.
     */
    public function pelangganDashboard()
    {
        $pendaftarans = Pendaftaran::with(['paketUmroh'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('pelanggan.dashboard', compact('pendaftarans'));
    }
}
