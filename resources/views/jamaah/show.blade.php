@extends('layouts.app')

@section('title', 'Detail Jamaah')

@section('content')
<a href="{{ route('pendaftaran.jamaah.index', $jamaah->pendaftaran) }}" class="text-decoration-none">&larr; Kembali ke daftar jamaah</a>
<div class="card mt-3"><div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <div><p class="text-muted mb-1">Data Jamaah</p><h2 class="fw-bold mb-0">{{ $jamaah->nama_lengkap }}</h2></div>
        <a href="{{ route('jamaah.edit', $jamaah) }}" class="btn btn-primary align-self-start">Ubah Data</a>
    </div>
    @include('jamaah._detail')
    <a href="{{ route('jamaah.dokumen.index', $jamaah) }}" class="btn btn-primary mt-4">Kelola Dokumen</a>
    @unless($jamaah->pendaftaran->canUploadDocuments())
        <div class="alert alert-warning mt-3 mb-0">Dokumen dapat diupload setelah pembayaran awal diverifikasi Valid oleh admin.</div>
    @endunless
    @if($jamaah->pendaftaran->canDeleteJamaah())
        <form method="POST" action="{{ route('jamaah.destroy', $jamaah) }}" class="mt-4" onsubmit="return confirm('Hapus data jamaah ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Hapus Data Jamaah</button>
        </form>
    @endif
</div></div>
@endsection
