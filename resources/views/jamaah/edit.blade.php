@extends('layouts.app')

@section('title', 'Ubah Data Jamaah')

@section('content')
<a href="{{ route('jamaah.show', $jamaah) }}" class="text-decoration-none">&larr; Kembali ke detail jamaah</a>
<div class="card mt-3"><div class="card-body p-4">
    <h2 class="fw-bold mb-4">Ubah Data Jamaah</h2>
    <form method="POST" action="{{ route('jamaah.update', $jamaah) }}">
        @csrf
        @method('PUT')
        @include('jamaah._form', ['submitLabel' => 'Simpan Perubahan'])
    </form>
</div></div>
@endsection
