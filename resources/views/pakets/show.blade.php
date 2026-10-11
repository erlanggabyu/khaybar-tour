@extends('layouts.app')

@section('title', $paketUmroh->nama_paket)

@section('content')
<div class="mb-4">
    <a href="{{ route('pakets.index') }}" class="text-decoration-none">&larr; Kembali ke daftar paket</a>
</div>

<div class="card border-top border-primary border-4 mb-4">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
            <div>
                <div class="d-flex gap-2 mb-2">
                    <span class="badge bg-primary">{{ $paketUmroh->jenis_perjalanan }}</span>
                    <span class="badge bg-success">Tersedia</span>
                </div>
                <p class="text-muted fw-bold mb-1">{{ $paketUmroh->kode_paket }}</p>
                <h1 class="fw-bold mb-0">{{ $paketUmroh->nama_paket }}</h1>
            </div>
            <div class="text-md-end">
                <p class="text-muted mb-1">Harga per jamaah</p>
                <h2 class="text-danger fw-bold">Rp {{ number_format($paketUmroh->harga, 0, ',', '.') }}</h2>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="bg-light rounded p-3 h-100">
                    <small class="text-muted">Durasi</small>
                    <div class="fw-bold">{{ $paketUmroh->durasi }} Hari</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light rounded p-3 h-100">
                    <small class="text-muted">Kota Keberangkatan</small>
                    <div class="fw-bold">{{ $paketUmroh->kota_keberangkatan }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light rounded p-3 h-100">
                    <small class="text-muted">Tanggal Berangkat</small>
                    <div class="fw-bold">{{ $paketUmroh->tanggal_keberangkatan->format('d M Y') }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light rounded p-3 h-100">
                    <small class="text-muted">Sisa Kuota</small>
                    <div class="fw-bold">{{ $paketUmroh->sisa_kuota }} / {{ $paketUmroh->kuota_jamaah }} Jamaah</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="fw-bold">Fasilitas</h5>
                <p class="text-muted" style="white-space: pre-line;">{{ $paketUmroh->fasilitas ?: 'Informasi fasilitas belum tersedia.' }}</p>
            </div>
            <div class="col-lg-4">
                <h5 class="fw-bold">Jadwal Perjalanan</h5>
                <p class="text-muted" style="white-space: pre-line;">{{ $paketUmroh->jadwal_perjalanan ?: 'Informasi jadwal belum tersedia.' }}</p>
            </div>
            <div class="col-lg-4">
                <h5 class="fw-bold">Persyaratan Dokumen</h5>
                <p class="text-muted" style="white-space: pre-line;">{{ $paketUmroh->persyaratan_dokumen ?: 'Informasi persyaratan belum tersedia.' }}</p>
            </div>
        </div>

        <div class="border-top mt-4 pt-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 class="fw-bold mb-1">Siap mendaftar paket ini?</h5>
                <p class="text-muted mb-0">Pastikan jumlah jamaah tidak melebihi sisa kuota.</p>
            </div>
            @auth
                @if(Auth::user()->isPelanggan())
                    <a href="{{ route('pendaftaran.create', $paketUmroh) }}" class="btn btn-primary btn-lg">Daftar Paket</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Login untuk Daftar</a>
            @endauth
        </div>
    </div>
</div>
@endsection
