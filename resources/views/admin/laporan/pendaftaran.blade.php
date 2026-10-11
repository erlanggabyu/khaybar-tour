@extends('layouts.app')
@section('title', 'Laporan Pendaftaran')
@section('styles')<style>@media print{nav,footer,.d-print-none{display:none!important}.card{box-shadow:none}.container{max-width:100%}}</style>@endsection
@section('content')@include('admin.laporan._nav')<h2 class="fw-bold">Laporan Pendaftaran</h2><p class="text-muted">Dicetak: {{ now()->format('d M Y H:i') }}</p>@include('admin.laporan._filter', ['type' => 'pendaftaran'])
<div class="card"><div class="table-responsive"><table class="table table-bordered mb-0"><thead><tr><th>Kode</th><th>Tanggal</th><th>Pelanggan</th><th>Paket</th><th>Jamaah</th><th>Status</th><th>Invoice</th></tr></thead><tbody>
@forelse($pendaftarans as $item)<tr><td>{{ $item->kode_pendaftaran }}</td><td>{{ $item->tanggal_pendaftaran->format('d M Y') }}</td><td>{{ $item->user->name }}</td><td>{{ $item->paketUmroh->nama_paket }}</td><td>{{ $item->jumlah_jamaah }}</td><td>{{ $item->status_pendaftaran }}</td><td>@if($item->invoice)<a href="{{ route('admin.invoice.show', $item->invoice) }}" target="_blank">{{ $item->invoice->kode_invoice }}</a>@else-@endif</td></tr>@empty<tr><td colspan="7" class="text-center">Tidak ada data.</td></tr>@endforelse
</tbody></table></div></div>@endsection
