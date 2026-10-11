@extends('layouts.app')

@section('title', 'Data Jamaah Pendaftaran')

@section('content')
<a href="{{ route('admin.pendaftaran.show', $pendaftaran) }}" class="text-decoration-none">&larr; Kembali ke detail pendaftaran</a>
<div class="my-4"><h2 class="fw-bold mb-1">Data Jamaah Pendaftaran</h2><p class="text-muted mb-0">{{ $pendaftaran->kode_pendaftaran }} - {{ $pendaftaran->user->name }}</p></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4">Nama</th><th>NIK</th><th>Jenis Kelamin</th><th>No. HP</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($pendaftaran->jamaahs as $jamaah)
            <tr><td class="ps-4 fw-bold">{{ $jamaah->nama_lengkap }}</td><td>{{ $jamaah->nik }}</td><td>{{ $jamaah->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td><td>{{ $jamaah->no_hp }}</td><td><a href="{{ route('admin.jamaah.show', $jamaah) }}" class="btn btn-primary btn-sm">Detail</a></td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5">Belum ada data jamaah.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div></div>
@endsection
