@extends('layouts.app')

@section('title', 'Detail Pendaftaran')

@section('content')
<a href="{{ route('admin.pendaftaran.index') }}" class="text-decoration-none">&larr; Kembali ke data pendaftaran</a>
<div class="card mt-3"><div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <div><p class="text-muted mb-1">Kode Pendaftaran</p><h2 class="fw-bold mb-0">{{ $pendaftaran->kode_pendaftaran }}</h2></div>
        <span class="badge align-self-start fs-6 {{ $pendaftaran->status_pendaftaran === 'Dibatalkan' ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $pendaftaran->status_pendaftaran }}</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6"><div class="bg-light rounded p-3"><small class="text-muted">Pelanggan</small><div class="fw-bold">{{ $pendaftaran->user->name }}</div><div>{{ $pendaftaran->user->email }}</div></div></div>
        <div class="col-md-6"><div class="bg-light rounded p-3"><small class="text-muted">Paket</small><div class="fw-bold">{{ $pendaftaran->paketUmroh->nama_paket }}</div><div>{{ $pendaftaran->jumlah_jamaah }} jamaah</div></div></div>
        <div class="col-md-6"><div class="bg-light rounded p-3"><small class="text-muted">Tanggal Pendaftaran</small><div class="fw-bold">{{ $pendaftaran->tanggal_pendaftaran->format('d M Y') }}</div></div></div>
        <div class="col-md-6"><div class="bg-light rounded p-3"><small class="text-muted">Total Harga</small><div class="fw-bold">Rp {{ number_format($pendaftaran->total_harga, 0, ',', '.') }}</div></div></div>
    </div>
    <h5 class="fw-bold mt-4">Catatan Pelanggan</h5>
    <p class="text-muted mb-0">{{ $pendaftaran->catatan_pelanggan ?: 'Tidak ada catatan.' }}</p>
    <a href="{{ route('admin.pendaftaran.jamaah.index', $pendaftaran) }}" class="btn btn-primary mt-4">Lihat Data Jamaah</a>
    <a href="{{ route('admin.pembayaran.index') }}" class="btn btn-outline-primary mt-4">Lihat Pembayaran</a>
</div></div>
@endsection
