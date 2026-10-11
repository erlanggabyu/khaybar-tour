@extends('layouts.app')
@section('title', 'Invoice Pendaftaran')
@section('content')
<div class="d-print-none mb-3"><a href="{{ route('pendaftaran.show', $invoice->pendaftaran) }}" class="text-decoration-none">&larr; Kembali ke detail pendaftaran</a></div>
<div class="card"><div class="card-body p-4 p-md-5">
    @include('invoice._content')
    <a href="{{ route('invoice.print', $invoice) }}" target="_blank" class="btn btn-primary d-print-none">Unduh / Cetak Invoice</a>
</div></div>
@endsection
