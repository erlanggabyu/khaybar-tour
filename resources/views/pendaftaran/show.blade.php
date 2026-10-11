@extends('layouts.app')

@section('title', 'Detail Pendaftaran')

@section('content')
<a href="{{ route('pendaftaran.index') }}" class="text-decoration-none">&larr; Kembali ke pendaftaran saya</a>
<div class="card mt-3"><div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <div><p class="text-muted mb-1">Kode Pendaftaran</p><h2 class="fw-bold mb-0">{{ $pendaftaran->kode_pendaftaran }}</h2></div>
        <span class="badge align-self-start fs-6 {{ $pendaftaran->status_pendaftaran === 'Dibatalkan' ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $pendaftaran->status_pendaftaran }}</span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="bg-light rounded p-3 h-100"><small class="text-muted">Paket Umroh</small><div class="fw-bold">{{ $pendaftaran->paketUmroh->nama_paket }}</div></div></div>
        <div class="col-md-3"><div class="bg-light rounded p-3 h-100"><small class="text-muted">Jumlah Jamaah</small><div class="fw-bold">{{ $pendaftaran->jumlah_jamaah }}</div></div></div>
        <div class="col-md-3"><div class="bg-light rounded p-3 h-100"><small class="text-muted">Total Harga</small><div class="fw-bold">Rp {{ number_format($pendaftaran->total_harga, 0, ',', '.') }}</div></div></div>
    </div>
    <h5 class="fw-bold">Catatan Pelanggan</h5>
    <p class="text-muted">{{ $pendaftaran->catatan_pelanggan ?: 'Tidak ada catatan.' }}</p>
    <a href="{{ route('pendaftaran.jamaah.index', $pendaftaran) }}" class="btn btn-primary mt-2">Kelola Data Jamaah</a>
    <a href="{{ route('pendaftaran.pembayaran.index', $pendaftaran) }}" class="btn btn-outline-primary mt-2">Lihat Tagihan Pembayaran</a>
    <a href="{{ route('pendaftaran.invoice.show', $pendaftaran) }}" class="btn btn-outline-dark mt-2">Lihat Invoice</a>

    @if($pendaftaran->canBeCancelled())
        <div class="alert alert-warning mt-4">Pendaftaran masih dapat dibatalkan karena pembayaran DP belum dilakukan.</div>
        <form method="POST" action="{{ route('pendaftaran.cancel', $pendaftaran) }}" onsubmit="return confirm('Batalkan pendaftaran ini?')">
            @csrf
            <button type="submit" class="btn btn-outline-danger">Batalkan Pendaftaran</button>
        </form>
    @endif
</div></div>
@endsection
