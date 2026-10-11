@extends('layouts.app')

@section('title', 'Dashboard Pelanggan')

@section('content')
<div class="row">
    <!-- Sidebar/Profile Info Card -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="display-5 fw-bold text-primary">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <h4 class="fw-bold mt-2">{{ Auth::user()->name }}</h4>
                    <span class="badge bg-secondary">Pelanggan</span>
                </div>
                <hr>
                <h5 class="fw-bold mb-3">Informasi Profil</h5>
                <p class="mb-2"><strong>Email:</strong> {{ Auth::user()->email }}</p>
                <p class="mb-2"><strong>No. HP:</strong> {{ Auth::user()->no_hp ?: '-' }}</p>
                <p class="mb-0 text-muted small">NIK dan identitas lengkap disimpan pada data jamaah masing-masing.</p>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Status Pendaftaran Umroh Anda</h4>
                    <a href="{{ url('/pakets') }}" class="btn btn-primary btn-sm px-3">Daftar Paket Baru</a>
                </div>
                <p class="text-muted">Kelola riwayat pendaftaran paket tour & travel umroh Anda di bawah ini.</p>

                <!-- Table Pendaftaran -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Paket</th>
                                <th>Keberangkatan</th>
                                <th>Jumlah Pax</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendaftarans ?? [] as $daftar)
                                <tr>
                                    <td><span class="fw-bold">{{ $daftar->kode_pendaftaran }}</span></td>
                                    <td>{{ $daftar->paketUmroh->nama_paket }}</td>
                                    <td>{{ $daftar->paketUmroh->tanggal_keberangkatan->format('d M Y') }}</td>
                                    <td>{{ $daftar->jumlah_jamaah }} Orang</td>
                                    <td>Rp {{ number_format($daftar->total_harga, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($daftar->status_pendaftaran === 'Draft') bg-secondary
                                            @elseif($daftar->status_pendaftaran === 'Lunas') bg-success
                                            @elseif($daftar->status_pendaftaran === 'Dibatalkan') bg-danger
                                            @else bg-warning text-dark
                                            @endif">
                                            {{ $daftar->status_pendaftaran }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('pendaftaran.show', $daftar) }}" class="btn btn-outline-primary btn-sm">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Anda belum memiliki riwayat pendaftaran paket umroh.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
