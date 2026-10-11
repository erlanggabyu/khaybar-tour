@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm bg-dark text-white p-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="fw-bold mb-1">Selamat Datang Kembali, {{ Auth::user()->name }}!</h4>
                        <p class="mb-0 text-white-50">Sebagai <strong>{{ Auth::user()->admin->jabatan ?? 'Staff' }}</strong>, Anda memiliki kendali penuh atas paket umroh, verifikasi berkas, pembayaran, dan pencetakan laporan.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-primary px-3 py-2 fs-6">Hari Ini: {{ date('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <!-- Total Paket -->
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card shadow-sm border-start border-primary border-4 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Total Paket Tour</h6>
                <h2 class="fw-bold mb-0 text-primary">{{ $totalPakets ?? 0 }}</h2>
                <small class="text-muted"><a href="{{ route('admin.pakets.index') }}" class="text-decoration-none">Kelola Paket &rarr;</a></small>
            </div>
        </div>
    </div>
    <!-- Total Pendaftar -->
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card shadow-sm border-start border-success border-4 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Total Pendaftaran</h6>
                <h2 class="fw-bold mb-0 text-success">{{ $totalPendaftarans ?? 0 }}</h2>
                <small class="text-muted"><a href="{{ route('admin.pendaftaran.index') }}" class="text-decoration-none">Lihat Pendaftar &rarr;</a></small>
            </div>
        </div>
    </div>
    <!-- Menunggu Verifikasi Pembayaran -->
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card shadow-sm border-start border-warning border-4 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Perlu Verifikasi Pembayaran</h6>
                <h2 class="fw-bold mb-0 text-warning">{{ $pendingPayments ?? 0 }}</h2>
                <small class="text-muted"><a href="{{ route('admin.pembayaran.index') }}" class="text-decoration-none text-warning fw-bold">Proses Verifikasi &rarr;</a></small>
            </div>
        </div>
    </div>
    <!-- Menunggu Verifikasi Dokumen -->
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card shadow-sm border-start border-info border-4 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Perlu Verifikasi Dokumen</h6>
                <h2 class="fw-bold mb-0 text-info">{{ $pendingDocuments ?? 0 }}</h2>
                <small class="text-muted"><a href="{{ route('admin.dokumen.index') }}" class="text-decoration-none text-info fw-bold">Proses Pemeriksaan &rarr;</a></small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Registrations -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">Pendaftaran Terbaru</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Pelanggan</th>
                                <th>Paket Umroh</th>
                                <th>Jumlah Pax</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPendaftarans ?? [] as $daftar)
                                <tr>
                                    <td><span class="fw-bold">{{ $daftar->kode_pendaftaran }}</span></td>
                                    <td>{{ $daftar->user->name }}</td>
                                    <td>{{ $daftar->paketUmroh->nama_paket }}</td>
                                    <td>{{ $daftar->jumlah_jamaah }} Pax</td>
                                    <td>
                                        <span class="badge 
                                            @if($daftar->status_pendaftaran === 'Draft') bg-secondary
                                            @elseif($daftar->status_pendaftaran === 'Lunas') bg-success
                                            @elseif($daftar->status_pendaftaran === 'Dibatalkan') bg-danger
                                            @else bg-warning text-dark
                                            @endif">
                                            {{ $daftar->status_pendaftaran }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.pendaftaran.show', $daftar) }}" class="btn btn-primary btn-sm py-1">Periksa</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Belum ada data pendaftaran masuk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">Menu Administrasi</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.pakets.create') }}" class="btn btn-outline-primary text-start p-3 fw-bold">
                        Tambah Paket Umroh Baru
                    </a>
                    <a href="{{ route('admin.laporan.pendaftaran') }}" class="btn btn-outline-success text-start p-3 fw-bold">
                        Cetak Laporan Pendaftaran/Pembayaran
                    </a>
                    <a href="{{ route('admin.dokumen.index') }}" class="btn btn-outline-dark text-start p-3 fw-bold">
                        Periksa Dokumen Jamaah
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
