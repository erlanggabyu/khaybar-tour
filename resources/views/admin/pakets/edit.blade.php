@extends('layouts.app')

@section('title', 'Ubah Paket Umroh')

@section('content')
<div class="mb-4">
    <h1 class="fw-bold mb-1">Ubah Paket Umroh</h1>
    <p class="text-muted mb-0">Perbarui informasi {{ $paketUmroh->nama_paket }}.</p>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('admin.pakets.update', $paketUmroh) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.pakets._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
</div>
@endsection
