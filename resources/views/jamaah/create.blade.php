@extends('layouts.app')

@section('title', 'Tambah Data Jamaah')

@section('content')
<a href="{{ route('pendaftaran.jamaah.index', $pendaftaran) }}" class="text-decoration-none">&larr; Kembali ke daftar jamaah</a>
<div class="card mt-3"><div class="card-body p-4">
    <h2 class="fw-bold mb-1">Tambah Data Jamaah</h2>
    <p class="text-muted">Pendaftaran {{ $pendaftaran->kode_pendaftaran }}</p>
    <form method="POST" action="{{ route('pendaftaran.jamaah.store', $pendaftaran) }}">
        @csrf
        @include('jamaah._form', ['jamaah' => null, 'submitLabel' => 'Simpan Data Jamaah'])
    </form>
</div></div>
@endsection
