@extends('layouts.app')

@section('title', 'Detail Jamaah')

@section('content')
<a href="{{ route('admin.pendaftaran.jamaah.index', $jamaah->pendaftaran) }}" class="text-decoration-none">&larr; Kembali ke data jamaah</a>
<div class="card mt-3"><div class="card-body p-4">
    <p class="text-muted mb-1">{{ $jamaah->pendaftaran->kode_pendaftaran }} - {{ $jamaah->pendaftaran->user->name }}</p>
    <h2 class="fw-bold mb-4">{{ $jamaah->nama_lengkap }}</h2>
    @include('jamaah._detail')
    <a href="{{ route('admin.dokumen.index') }}" class="btn btn-primary mt-4">Lihat Dokumen Jamaah</a>
</div></div>
@endsection
