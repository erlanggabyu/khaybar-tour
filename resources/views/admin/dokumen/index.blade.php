@extends('layouts.app')

@section('title', 'Verifikasi Dokumen')

@section('content')
<div class="mb-4"><h2 class="fw-bold mb-1">Verifikasi Dokumen</h2><p class="text-muted mb-0">Dokumen seluruh jamaah. Dokumen menunggu pemeriksaan ditampilkan lebih dahulu.</p></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4">Jamaah</th><th>Pelanggan</th><th>Jenis</th><th>Status</th><th>Verifier</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($dokumens as $dokumen)
            <tr>
                <td class="ps-4 fw-bold">{{ $dokumen->jamaah->nama_lengkap }}</td>
                <td>{{ $dokumen->jamaah->pendaftaran->user->name }}</td>
                <td>{{ $dokumen->jenis_dokumen }}</td>
                <td><span class="badge @if($dokumen->status_dokumen === 'Valid') bg-success @elseif(in_array($dokumen->status_dokumen, ['Tidak Valid', 'Perlu Revisi'])) bg-danger @else bg-warning text-dark @endif">{{ $dokumen->status_dokumen }}</span></td>
                <td>{{ $dokumen->verifier?->name ?: '-' }}</td>
                <td><a href="{{ route('admin.dokumen.show', $dokumen) }}" class="btn btn-primary btn-sm">Periksa</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada dokumen.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div></div>
<div class="mt-3">{{ $dokumens->links() }}</div>
@endsection
