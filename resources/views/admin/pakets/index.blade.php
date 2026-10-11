@extends('layouts.app')

@section('title', 'Kelola Paket Umroh')

@section('content')
@php
    $statusClasses = [
        'tersedia' => 'bg-success',
        'penuh' => 'bg-warning text-dark',
        'ditutup' => 'bg-secondary',
        'selesai' => 'bg-dark',
    ];
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1">Kelola Paket Umroh</h1>
        <p class="text-muted mb-0">Tambah, ubah, nonaktifkan, dan atur status paket.</p>
    </div>
    <a href="{{ route('admin.pakets.create') }}" class="btn btn-primary">Tambah Paket Baru</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Paket</th>
                        <th>Keberangkatan</th>
                        <th>Harga</th>
                        <th>Kuota</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pakets as $paket)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $paket->nama_paket }}</div>
                                <small class="text-muted">{{ $paket->kode_paket }} | {{ $paket->jenis_perjalanan }} | {{ $paket->durasi }} hari</small>
                            </td>
                            <td>
                                {{ $paket->tanggal_keberangkatan->format('d M Y') }}
                                <small class="d-block text-muted">{{ $paket->kota_keberangkatan }}</small>
                            </td>
                            <td>Rp {{ number_format($paket->harga, 0, ',', '.') }}</td>
                            <td>{{ $paket->sisa_kuota }} / {{ $paket->kuota_jamaah }}</td>
                            <td>
                                <span class="badge {{ $statusClasses[$paket->status_paket] ?? 'bg-secondary' }}">
                                    {{ ucfirst($paket->status_paket) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <a href="{{ route('admin.pakets.edit', $paket) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                                    <form action="{{ route('admin.pakets.status', $paket) }}" method="POST" class="d-flex gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status_paket" class="form-select form-select-sm" aria-label="Ubah status {{ $paket->nama_paket }}">
                                            @foreach(\App\Models\PaketUmroh::statuses() as $status)
                                                <option value="{{ $status }}" @selected($paket->status_paket === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-dark">Simpan</button>
                                    </form>

                                    @if($paket->status_paket !== \App\Models\PaketUmroh::STATUS_DITUTUP)
                                        <form action="{{ route('admin.pakets.deactivate', $paket) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Nonaktifkan</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada data paket umroh.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $pakets->links() }}
</div>
@endsection
