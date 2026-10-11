@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="mb-4"><h2 class="fw-bold mb-1">Verifikasi Pembayaran</h2><p class="text-muted mb-0">Seluruh pembayaran pelanggan. Pembayaran menunggu verifikasi ditampilkan lebih dahulu.</p></div>
<h4 class="fw-bold mb-3">DP dan Pelunasan Langsung</h4>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4">Pendaftaran</th><th>Pelanggan</th><th>Jenis</th><th>Nominal</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($pembayarans as $pembayaran)
            <tr>
                <td class="ps-4 fw-bold">{{ $pembayaran->pendaftaran->kode_pendaftaran }}</td>
                <td>{{ $pembayaran->pendaftaran->user->name }}</td>
                <td>{{ $pembayaran->jenis_pembayaran }}</td>
                <td>Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</td>
                <td><span class="badge @if($pembayaran->status_pembayaran === 'Valid') bg-success @elseif($pembayaran->status_pembayaran === 'Tidak Valid') bg-danger @else bg-warning text-dark @endif">{{ $pembayaran->status_pembayaran }}</span></td>
                <td><a href="{{ route('admin.pembayaran.show', $pembayaran) }}" class="btn btn-primary btn-sm">Detail</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada tagihan pembayaran.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div></div>
<div class="mt-3">{{ $pembayarans->links() }}</div>

<h4 class="fw-bold mt-5 mb-3">Cicilan</h4>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4">Pendaftaran</th><th>Pelanggan</th><th>Cicilan</th><th>Nominal</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($cicilans as $cicilan)
            <tr>
                <td class="ps-4 fw-bold">{{ $cicilan->pendaftaran->kode_pendaftaran }}</td>
                <td>{{ $cicilan->pendaftaran->user->name }}</td>
                <td>{{ $cicilan->label() }}</td>
                <td>Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}</td>
                <td><span class="badge @if($cicilan->status === 'Valid') bg-success @elseif($cicilan->status === 'Tidak Valid') bg-danger @else bg-warning text-dark @endif">{{ $cicilan->status }}</span></td>
                <td><a href="{{ route('admin.cicilan.show', $cicilan) }}" class="btn btn-primary btn-sm">Detail</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada tagihan cicilan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div></div>
<div class="mt-3">{{ $cicilans->links() }}</div>
@endsection
