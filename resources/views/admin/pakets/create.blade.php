@extends('layouts.app')

@section('title', 'Tambah Paket Umroh')

@section('content')
<div class="mb-4">
    <h1 class="fw-bold mb-1">Tambah Paket Umroh</h1>
    <p class="text-muted mb-0">Lengkapi data paket yang akan ditawarkan kepada pelanggan.</p>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('admin.pakets.store') }}" method="POST">
            @csrf
            @include('admin.pakets._form', ['submitLabel' => 'Simpan Paket'])
        </form>
    </div>
</div>
@endsection
