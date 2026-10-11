<?php

use App\Http\Controllers\Admin\CicilanController as AdminCicilanController;
use App\Http\Controllers\Admin\DokumenController as AdminDokumenController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\JamaahController as AdminJamaahController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\PaketUmrohController as AdminPaketUmrohController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\PendaftaranController as AdminPendaftaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CicilanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JamaahController;
use App\Http\Controllers\PaketUmrohController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PendaftaranController;
use App\Models\PaketUmroh;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $pakets = PaketUmroh::tersedia()
        ->orderBy('tanggal_keberangkatan')
        ->take(3)
        ->get();

    return view('welcome', compact('pakets'));
})->name('home');

Route::get('/pakets', [PaketUmrohController::class, 'index'])->name('pakets.index');
Route::get('/pakets/{paketUmroh}', [PaketUmrohController::class, 'show'])->name('pakets.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPost'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerPost'])->middleware('throttle:5,1')->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dokumen/{dokumen}/file', [DokumenController::class, 'download'])->name('dokumen.download');
    Route::get('/pembayaran/{pembayaran}/file', [PembayaranController::class, 'download'])->name('pembayaran.download');
    Route::get('/cicilan/{cicilan}/file', [CicilanController::class, 'download'])->name('cicilan.download');

    Route::middleware('role:pelanggan')->group(function () {
        Route::get('/pelanggan/dashboard', [DashboardController::class, 'pelangganDashboard'])->name('pelanggan.dashboard');
        Route::get('/pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
        Route::get('/pendaftaran/{pendaftaran}', [PendaftaranController::class, 'show'])->name('pendaftaran.show');
        Route::get('/paket/{paketUmroh}/daftar', [PendaftaranController::class, 'create'])->name('pendaftaran.create');
        Route::post('/paket/{paketUmroh}/daftar', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
        Route::post('/pendaftaran/{pendaftaran}/batal', [PendaftaranController::class, 'cancel'])->name('pendaftaran.cancel');
        Route::get('/pendaftaran/{pendaftaran}/jamaah', [JamaahController::class, 'index'])->name('pendaftaran.jamaah.index');
        Route::get('/pendaftaran/{pendaftaran}/jamaah/create', [JamaahController::class, 'create'])->name('pendaftaran.jamaah.create');
        Route::post('/pendaftaran/{pendaftaran}/jamaah', [JamaahController::class, 'store'])->name('pendaftaran.jamaah.store');
        Route::get('/jamaah/{jamaah}', [JamaahController::class, 'show'])->name('jamaah.show');
        Route::get('/jamaah/{jamaah}/edit', [JamaahController::class, 'edit'])->name('jamaah.edit');
        Route::put('/jamaah/{jamaah}', [JamaahController::class, 'update'])->name('jamaah.update');
        Route::delete('/jamaah/{jamaah}', [JamaahController::class, 'destroy'])->name('jamaah.destroy');
        Route::get('/jamaah/{jamaah}/dokumen', [DokumenController::class, 'index'])->name('jamaah.dokumen.index');
        Route::get('/jamaah/{jamaah}/dokumen/create', [DokumenController::class, 'create'])->name('jamaah.dokumen.create');
        Route::post('/jamaah/{jamaah}/dokumen', [DokumenController::class, 'store'])->name('jamaah.dokumen.store');
        Route::delete('/dokumen/{dokumen}', [DokumenController::class, 'destroy'])->name('dokumen.destroy');
        Route::get('/pendaftaran/{pendaftaran}/pembayaran', [PembayaranController::class, 'index'])->name('pendaftaran.pembayaran.index');
        Route::get('/pembayaran/{pembayaran}/upload', [PembayaranController::class, 'upload'])->name('pembayaran.upload');
        Route::post('/pembayaran/{pembayaran}/upload', [PembayaranController::class, 'store'])->name('pembayaran.store');
        Route::get('/cicilan/{cicilan}/upload', [CicilanController::class, 'upload'])->name('cicilan.upload');
        Route::post('/cicilan/{cicilan}/upload', [CicilanController::class, 'store'])->name('cicilan.store');
        Route::get('/pendaftaran/{pendaftaran}/invoice', [InvoiceController::class, 'show'])->name('pendaftaran.invoice.show');
        Route::get('/invoice/{invoice}/cetak', [InvoiceController::class, 'print'])->name('invoice.print');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::get('/admin/pendaftaran', [AdminPendaftaranController::class, 'index'])->name('admin.pendaftaran.index');
        Route::get('/admin/pendaftaran/{pendaftaran}', [AdminPendaftaranController::class, 'show'])->name('admin.pendaftaran.show');
        Route::get('/admin/pendaftaran/{pendaftaran}/jamaah', [AdminJamaahController::class, 'index'])->name('admin.pendaftaran.jamaah.index');
        Route::get('/admin/jamaah/{jamaah}', [AdminJamaahController::class, 'show'])->name('admin.jamaah.show');
        Route::get('/admin/dokumen', [AdminDokumenController::class, 'index'])->name('admin.dokumen.index');
        Route::get('/admin/dokumen/{dokumen}', [AdminDokumenController::class, 'show'])->name('admin.dokumen.show');
        Route::post('/admin/dokumen/{dokumen}/verifikasi', [AdminDokumenController::class, 'verify'])->name('admin.dokumen.verify');
        Route::get('/admin/pembayaran', [AdminPembayaranController::class, 'index'])->name('admin.pembayaran.index');
        Route::get('/admin/pembayaran/{pembayaran}', [AdminPembayaranController::class, 'show'])->name('admin.pembayaran.show');
        Route::post('/admin/pembayaran/{pembayaran}/verifikasi', [AdminPembayaranController::class, 'verify'])->name('admin.pembayaran.verify');
        Route::get('/admin/cicilan/{cicilan}', [AdminCicilanController::class, 'show'])->name('admin.cicilan.show');
        Route::post('/admin/cicilan/{cicilan}/verifikasi', [AdminCicilanController::class, 'verify'])->name('admin.cicilan.verify');
        Route::get('/admin/invoice/{invoice}', [AdminInvoiceController::class, 'show'])->name('admin.invoice.show');
        Route::get('/admin/laporan/pendaftaran', [AdminLaporanController::class, 'pendaftaran'])->name('admin.laporan.pendaftaran');
        Route::get('/admin/laporan/pembayaran', [AdminLaporanController::class, 'pembayaran'])->name('admin.laporan.pembayaran');
        Route::get('/admin/laporan/jamaah', [AdminLaporanController::class, 'jamaah'])->name('admin.laporan.jamaah');
        Route::prefix('admin/pakets')->name('admin.pakets.')->group(function () {
            Route::get('/', [AdminPaketUmrohController::class, 'index'])->name('index');
            Route::get('/create', [AdminPaketUmrohController::class, 'create'])->name('create');
            Route::post('/', [AdminPaketUmrohController::class, 'store'])->name('store');
            Route::get('/{paketUmroh}/edit', [AdminPaketUmrohController::class, 'edit'])->name('edit');
            Route::put('/{paketUmroh}', [AdminPaketUmrohController::class, 'update'])->name('update');
            Route::patch('/{paketUmroh}/status', [AdminPaketUmrohController::class, 'updateStatus'])->name('status');
            Route::patch('/{paketUmroh}/deactivate', [AdminPaketUmrohController::class, 'deactivate'])->name('deactivate');
        });
    });
});
