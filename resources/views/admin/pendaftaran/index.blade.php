@extends('layouts.app')

@section('title', 'Data Pendaftaran')

@section('content')
<div class="mb-4"><h2 class="fw-bold mb-1">Data Pendaftaran</h2><p class="text-muted mb-0">Seluruh pendaftaran paket umroh pelanggan.</p></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4">Kode</th><th>Pelanggan</th><th>Paket</th><th>Jamaah</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($pendaftarans as $pendaftaran)
            <tr>
                <td class="ps-4 fw-bold">{{ $pendaftaran->kode_pendaftaran }}</td>
                <td>{{ $pendaftaran->user->name }}</td>
                <td>{{ $pendaftaran->paketUmroh->nama_paket }}</td>
                <td>{{ $pendaftaran->jumlah_jamaah }}</td>
                <td><span class="badge {{ $pendaftaran->status_pendaftaran === 'Dibatalkan' ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $pendaftaran->status_pendaftaran }}</span></td>
                <td><a href="{{ route('admin.pendaftaran.show', $pendaftaran) }}" class="btn btn-primary btn-sm">Detail</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada pendaftaran.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div></div>
<div class="mt-3">{{ $pendaftarans->links() }}</div>
@endsection
