@extends('layouts.app')

@section('title', 'Paket Umroh Tersedia')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
    <div>
        <span class="badge bg-primary mb-2">Katalog Khaybar Tour</span>
        <h1 class="fw-bold mb-1">Paket Umroh Tersedia</h1>
        <p class="text-muted mb-0">Pilih perjalanan yang sesuai dengan kebutuhan ibadah Anda.</p>
    </div>
    @auth
        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.pakets.index') }}" class="btn btn-dark">Kelola Paket</a>
        @endif
    @endauth
</div>

<div class="row">
    @forelse($pakets as $paket)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-top border-primary border-4">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <span class="badge bg-primary">{{ $paket->jenis_perjalanan }}</span>
                        <span class="badge bg-success">Tersedia</span>
                    </div>

                    <p class="small text-muted fw-bold mb-1">{{ $paket->kode_paket }}</p>
                    <h4 class="fw-bold">{{ $paket->nama_paket }}</h4>
                    <h3 class="text-danger fw-bold mb-3">Rp {{ number_format($paket->harga, 0, ',', '.') }}</h3>

                    <dl class="row small mb-3">
                        <dt class="col-5">Durasi</dt>
                        <dd class="col-7">{{ $paket->durasi }} hari</dd>
                        <dt class="col-5">Berangkat</dt>
                        <dd class="col-7">{{ $paket->tanggal_keberangkatan->format('d M Y') }}</dd>
                        <dt class="col-5">Kota</dt>
                        <dd class="col-7">{{ $paket->kota_keberangkatan }}</dd>
                        <dt class="col-5">Sisa kuota</dt>
                        <dd class="col-7">{{ $paket->sisa_kuota }} jamaah</dd>
                    </dl>

                    <a href="{{ route('pakets.show', $paket) }}" class="btn btn-primary mt-auto">Lihat Detail Paket</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center py-5">
                Belum ada paket umroh yang tersedia saat ini.
            </div>
        </div>
    @endforelse
</div>

{{ $pakets->links() }}
@endsection
