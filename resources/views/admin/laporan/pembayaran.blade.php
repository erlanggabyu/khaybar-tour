@extends('layouts.app')
@section('title', 'Laporan Pembayaran')
@section('styles')<style>@media print{nav,footer,.d-print-none{display:none!important}.card{box-shadow:none}.container{max-width:100%}}</style>@endsection
@section('content')@include('admin.laporan._nav')<h2 class="fw-bold">Laporan Pembayaran</h2><p class="text-muted">Dicetak: {{ now()->format('d M Y H:i') }}</p>@include('admin.laporan._filter', ['type' => 'pembayaran'])
<div class="card"><div class="table-responsive"><table class="table table-bordered mb-0"><thead><tr><th>Tanggal</th><th>Pendaftaran</th><th>Pelanggan</th><th>Paket</th><th>Jenis</th><th>Nominal</th><th>Status</th></tr></thead><tbody>
@forelse($pembayarans as $item)<tr><td>{{ $item->created_at->format('d M Y') }}</td><td>{{ $item->pendaftaran->kode_pendaftaran }}</td><td>{{ $item->pendaftaran->user->name }}</td><td>{{ $item->pendaftaran->paketUmroh->nama_paket }}</td><td>{{ $item->jenis_pembayaran }}</td><td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td><td>{{ $item->status_pembayaran }}</td></tr>@empty<tr><td colspan="7" class="text-center">Tidak ada data.</td></tr>@endforelse
</tbody><tfoot><tr><th colspan="5">Total Pembayaran Valid</th><th colspan="2">Rp {{ number_format($pembayarans->where('status_pembayaran', 'Valid')->sum('nominal'), 0, ',', '.') }}</th></tr></tfoot></table></div></div>@endsection
