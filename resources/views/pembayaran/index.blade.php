@extends('layouts.app')

@section('title', 'Tagihan Pembayaran')

@section('content')
@php
    $dp = $pendaftaran->pembayarans->firstWhere('jenis_pembayaran', \App\Models\Pembayaran::JENIS_DP);
    $pelunasan = $pendaftaran->pembayarans->firstWhere('jenis_pembayaran', \App\Models\Pembayaran::JENIS_PELUNASAN);
    $dpValid = $dp?->status_pembayaran === \App\Models\Pembayaran::STATUS_VALID;
    $cicilanStarted = $pendaftaran->cicilans->contains(fn ($item) => $item->status !== \App\Models\Cicilan::STATUS_BELUM_BAYAR || filled($item->bukti_file));
    $pelunasanStarted = $pelunasan && ($pelunasan->status_pembayaran !== \App\Models\Pembayaran::STATUS_BELUM_BAYAR || filled($pelunasan->bukti_file));
@endphp
<a href="{{ route('pendaftaran.show', $pendaftaran) }}" class="text-decoration-none">&larr; Kembali ke detail pendaftaran</a>
<div class="my-4">
    <h2 class="fw-bold mb-1">Tagihan Pembayaran</h2>
    <p class="text-muted mb-0">{{ $pendaftaran->kode_pendaftaran }} - {{ $pendaftaran->paketUmroh->nama_paket }}</p>
</div>
<div class="alert alert-info">DP ditetapkan sebesar 30% dari total harga. Setelah DP Valid, pelanggan dapat memilih pelunasan langsung atau cicilan maksimal 4 kali dari sisa tagihan.</div>
<div class="row g-4 mb-4">
@foreach($pendaftaran->pembayarans->sortBy(fn ($item) => $item->jenis_pembayaran === 'DP' ? 0 : 1) as $pembayaran)
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body p-4">
            <div class="d-flex justify-content-between gap-3 mb-3">
                <h3 class="fw-bold mb-0">{{ $pembayaran->jenis_pembayaran }}</h3>
                <span class="badge align-self-start @if($pembayaran->status_pembayaran === 'Valid') bg-success @elseif($pembayaran->status_pembayaran === 'Tidak Valid') bg-danger @else bg-warning text-dark @endif">{{ $pembayaran->status_pembayaran }}</span>
            </div>
            <p class="text-muted mb-1">Nominal Tagihan</p>
            <h4 class="text-danger fw-bold">Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</h4>
            @if($pembayaran->bukti_file)
                <p class="mt-3 mb-1"><strong>Bukti:</strong> <a href="{{ route('pembayaran.download', $pembayaran) }}">{{ $pembayaran->nama_file }}</a></p>
            @endif
            @if($pembayaran->catatan_admin)
                <div class="alert alert-danger mt-3 mb-0">{{ $pembayaran->catatan_admin }}</div>
            @endif
            @if($pembayaran->canBeUploaded() && $pembayaran->isAvailableForCustomer())
                <a href="{{ route('pembayaran.upload', $pembayaran) }}" class="btn btn-primary mt-3">{{ $pembayaran->status_pembayaran === 'Tidak Valid' ? 'Upload Ulang Bukti' : 'Upload Bukti Pembayaran' }}</a>
            @elseif($pembayaran->jenis_pembayaran === \App\Models\Pembayaran::JENIS_PELUNASAN && $cicilanStarted)
                <p class="text-muted mt-3 mb-0">Pelunasan langsung terkunci karena cicilan sudah dimulai.</p>
            @elseif(!$pembayaran->isAvailableForCustomer())
                <p class="text-muted mt-3 mb-0">Menunggu pembayaran DP dinyatakan Valid.</p>
            @endif
        </div></div>
    </div>
@endforeach
</div>

<div class="mb-3">
    <h3 class="fw-bold mb-1">Opsi Cicilan</h3>
    <p class="text-muted mb-0">Sisa tagihan setelah DP dibagi menjadi 4 cicilan.</p>
</div>
<div class="row g-4">
@foreach($pendaftaran->cicilans->sortBy('cicilan_ke') as $cicilan)
    <div class="col-lg-3 col-md-6">
        <div class="card h-100"><div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h4 class="fw-bold mb-0">{{ $cicilan->label() }}</h4>
                <span class="badge @if($cicilan->status === 'Valid') bg-success @elseif($cicilan->status === 'Tidak Valid') bg-danger @else bg-warning text-dark @endif">{{ $cicilan->status }}</span>
            </div>
            <p class="text-muted mb-1">Nominal</p>
            <h5 class="text-danger fw-bold">Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}</h5>
            <p class="small text-muted mb-2">Jatuh tempo: {{ $cicilan->due_date->format('d M Y') }}</p>
            @if($cicilan->bukti_file)
                <p class="small mb-2"><a href="{{ route('cicilan.download', $cicilan) }}">{{ $cicilan->nama_file }}</a></p>
            @endif
            @if($cicilan->catatan_admin)
                <div class="alert alert-danger small py-2">{{ $cicilan->catatan_admin }}</div>
            @endif
            @if($cicilan->canBeUploaded() && $cicilan->isAvailableForCustomer())
                <a href="{{ route('cicilan.upload', $cicilan) }}" class="btn btn-primary btn-sm">{{ $cicilan->status === 'Tidak Valid' ? 'Upload Ulang' : 'Upload Bukti' }}</a>
            @elseif(! $dpValid)
                <p class="text-muted small mb-0">Aktif setelah DP Valid.</p>
            @elseif($pelunasanStarted)
                <p class="text-muted small mb-0">Terkunci karena pelunasan langsung sudah dimulai.</p>
            @endif
        </div></div>
    </div>
@endforeach
</div>
@endsection
