@extends('layouts.app')
@section('title', 'Rekap Jamaah per Paket')
@section('styles')<style>@media print{nav,footer,.d-print-none{display:none!important}.card{box-shadow:none}.container{max-width:100%}}</style>@endsection
@section('content')@include('admin.laporan._nav')<h2 class="fw-bold">Rekap Jumlah Jamaah per Paket</h2><p class="text-muted">Dicetak: {{ now()->format('d M Y H:i') }}</p>@include('admin.laporan._filter', ['type' => 'jamaah'])
<div class="card"><div class="table-responsive"><table class="table table-bordered mb-0"><thead><tr><th>Kode Paket</th><th>Nama Paket</th><th>Kuota</th><th>Jumlah Pendaftaran</th><th>Jamaah Terdaftar</th></tr></thead><tbody>
@forelse($pakets as $paket)<tr><td>{{ $paket->kode_paket }}</td><td>{{ $paket->nama_paket }}</td><td>{{ $paket->kuota_jamaah }}</td><td>{{ $paket->jumlah_pendaftaran }}</td><td>{{ $paket->jumlah_jamaah_terdaftar ?? 0 }}</td></tr>@empty<tr><td colspan="5" class="text-center">Tidak ada data.</td></tr>@endforelse
</tbody></table></div></div>@endsection
