@extends('layouts.app')

@section('title', 'Dokumen Jamaah')

@section('content')
@php($canUploadDocuments = $jamaah->pendaftaran->canUploadDocuments())
<a href="{{ route('jamaah.show', $jamaah) }}" class="text-decoration-none">&larr; Kembali ke detail jamaah</a>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 my-4">
    <div>
        <h2 class="fw-bold mb-1">Dokumen {{ $jamaah->nama_lengkap }}</h2>
        <p class="text-muted mb-0">{{ $jamaah->pendaftaran->kode_pendaftaran }} - {{ $jamaah->pendaftaran->paketUmroh->nama_paket }}</p>
    </div>
    @if($canUploadDocuments)
        <a href="{{ route('jamaah.dokumen.create', $jamaah) }}" class="btn btn-primary">Upload Dokumen</a>
    @endif
</div>

@unless($canUploadDocuments)
    <div class="alert alert-warning">Upload dokumen baru dapat dilakukan setelah pembayaran awal, seperti DP, sudah diverifikasi Valid oleh admin.</div>
@endunless

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4">Jenis</th><th>Nama File</th><th>Ukuran</th><th>Status</th><th>Catatan Admin</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($jamaah->dokumens as $dokumen)
            <tr>
                <td class="ps-4 fw-bold">{{ $dokumen->jenis_dokumen }}</td>
                <td><a href="{{ route('dokumen.download', $dokumen) }}">{{ $dokumen->nama_file }}</a></td>
                <td>{{ number_format($dokumen->ukuran_file / 1024, 1, ',', '.') }} KB</td>
                <td><span class="badge @if($dokumen->status_dokumen === 'Valid') bg-success @elseif(in_array($dokumen->status_dokumen, ['Tidak Valid', 'Perlu Revisi'])) bg-danger @else bg-warning text-dark @endif">{{ $dokumen->status_dokumen }}</span></td>
                <td>{{ $dokumen->catatan_admin ?: '-' }}</td>
                <td>
                    @if($canUploadDocuments && $dokumen->canBeReuploaded())
                        <a href="{{ route('jamaah.dokumen.create', $jamaah) }}" class="btn btn-outline-primary btn-sm">Upload Ulang</a>
                    @endif
                    @if($dokumen->canBeDeletedByCustomer())
                        <form method="POST" action="{{ route('dokumen.destroy', $dokumen) }}" class="d-inline" onsubmit="return confirm('Hapus dokumen ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada dokumen yang diupload.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div></div>
@endsection
