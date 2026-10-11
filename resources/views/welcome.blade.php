@extends('layouts.app')

@section('title', 'Selamat Datang')

@section('content')
<section class="card border-0 overflow-hidden mb-5 text-white" style="background: linear-gradient(125deg, #073d31, #0b5d49 65%, #16836a);">
    <div class="card-body p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge text-bg-light text-primary mb-3">Pendaftaran Umroh Online</span>
                <h1 class="display-5 fw-bold">Perjalanan umroh nyaman dan terpercaya</h1>
                <p class="lead text-white-50">Pilih paket, daftarkan jamaah, lengkapi dokumen, dan pantau pembayaran dalam satu sistem.</p>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="#daftar-paket" class="btn btn-light btn-lg text-primary">Lihat Paket</a>
                    @guest<a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Daftar Akun</a>@endguest
                </div>
            </div>
            <div class="col-lg-4"><div class="bg-white bg-opacity-10 rounded-4 p-4"><div class="small text-uppercase text-white-50">Layanan Terintegrasi</div><div class="h4 fw-bold mt-2 mb-0">Paket, jamaah, dokumen, pembayaran, dan invoice</div></div></div>
        </div>
    </div>
</section>

<section class="row g-4 mb-5 text-center">
    @foreach([
        ['01', 'Aman dan Terverifikasi', 'Akses berbasis role, file privat, dan setiap verifikasi dilakukan admin.'],
        ['02', 'Informasi Paket Lengkap', 'Harga, jadwal, fasilitas, persyaratan, dan sisa kuota dapat dilihat dengan jelas.'],
        ['03', 'Proses Digital Ringkas', 'Kelola pendaftaran sampai invoice secara online dari akun pelanggan.'],
    ] as [$number, $title, $description])
        <div class="col-md-4"><div class="card h-100"><div class="card-body p-4"><span class="badge bg-primary rounded-pill fs-6 mb-3">{{ $number }}</span><h2 class="h5 fw-bold">{{ $title }}</h2><p class="text-muted mb-0">{{ $description }}</p></div></div></div>
    @endforeach
</section>

<section id="daftar-paket">
    <div class="text-center page-heading"><h2 class="fw-bold">Paket Umroh Tersedia</h2><p class="text-muted mb-0">Pilih paket yang sesuai dengan kebutuhan perjalanan ibadah Anda.</p></div>
    <div class="row g-4">
        @forelse($pakets ?? [] as $paket)
            @php($percent = min(100, ($paket->sisa_kuota / max(1, $paket->kuota_jamaah)) * 100))
            <div class="col-lg-4 col-md-6">
                <article class="card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between gap-2 mb-3"><span class="badge bg-primary">{{ $paket->jenis_perjalanan }}</span><span class="small text-muted">{{ $paket->durasi }} hari</span></div>
                        <h3 class="h5 fw-bold">{{ $paket->nama_paket }}</h3>
                        <div class="h4 fw-bold text-primary mt-3">Rp {{ number_format($paket->harga, 0, ',', '.') }}</div>
                        <dl class="row small mt-4 mb-3"><dt class="col-5">Berangkat</dt><dd class="col-7">{{ $paket->tanggal_keberangkatan->format('d M Y') }}</dd><dt class="col-5">Dari</dt><dd class="col-7">{{ $paket->kota_keberangkatan }}</dd><dt class="col-5">Sisa kuota</dt><dd class="col-7">{{ $paket->sisa_kuota }} dari {{ $paket->kuota_jamaah }}</dd></dl>
                        <div class="progress" style="height: 7px;"><div class="progress-bar bg-success" style="width: {{ $percent }}%"></div></div>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-4 pt-0"><a href="{{ route('pakets.show', $paket) }}" class="btn btn-primary w-100">Lihat Detail</a></div>
                </article>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info text-center">Belum ada paket umroh yang tersedia.</div></div>
        @endforelse
    </div>
    <div class="text-center mt-4"><a href="{{ route('pakets.index') }}" class="btn btn-outline-primary">Lihat Semua Paket</a></div>
</section>
@endsection
