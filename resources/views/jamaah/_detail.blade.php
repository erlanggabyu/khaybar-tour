<div class="row g-3">
    <div class="col-md-6"><div class="bg-light rounded p-3 h-100"><small class="text-muted">NIK</small><div class="fw-bold">{{ $jamaah->nik }}</div></div></div>
    <div class="col-md-6"><div class="bg-light rounded p-3 h-100"><small class="text-muted">Jenis Kelamin</small><div class="fw-bold">{{ $jamaah->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</div></div></div>
    <div class="col-md-6"><div class="bg-light rounded p-3 h-100"><small class="text-muted">Tempat, Tanggal Lahir</small><div class="fw-bold">{{ $jamaah->tempat_lahir ?: '-' }}, {{ $jamaah->tanggal_lahir->format('d M Y') }}</div></div></div>
    <div class="col-md-6"><div class="bg-light rounded p-3 h-100"><small class="text-muted">Kontak</small><div class="fw-bold">{{ $jamaah->no_hp }}</div><div>{{ $jamaah->email ?: '-' }}</div></div></div>
    <div class="col-12"><div class="bg-light rounded p-3"><small class="text-muted">Alamat</small><div class="fw-bold">{{ $jamaah->alamat }}</div></div></div>
    <div class="col-md-4"><div class="bg-light rounded p-3 h-100"><small class="text-muted">No. Paspor</small><div class="fw-bold">{{ $jamaah->no_paspor ?: '-' }}</div></div></div>
    <div class="col-md-4"><div class="bg-light rounded p-3 h-100"><small class="text-muted">Tanggal Terbit Paspor</small><div class="fw-bold">{{ $jamaah->tanggal_terbit_paspor?->format('d M Y') ?: '-' }}</div></div></div>
    <div class="col-md-4"><div class="bg-light rounded p-3 h-100"><small class="text-muted">Tanggal Berakhir Paspor</small><div class="fw-bold">{{ $jamaah->tanggal_berakhir_paspor?->format('d M Y') ?: '-' }}</div></div></div>
</div>
