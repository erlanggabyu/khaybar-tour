<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div><h2 class="fw-bold mb-1">INVOICE</h2><p class="text-muted mb-0">Khaybar Tour & Travel Umroh</p></div>
    <div class="text-end"><div class="fw-bold">{{ $invoice->kode_invoice }}</div><div>{{ $invoice->tanggal_invoice->format('d M Y') }}</div></div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted">Pelanggan</small><div class="fw-bold">{{ $invoice->pendaftaran->user->name }}</div><div>{{ $invoice->pendaftaran->user->email }}</div><div>{{ $invoice->pendaftaran->user->no_hp ?: '-' }}</div></div></div>
    <div class="col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted">Paket Umroh</small><div class="fw-bold">{{ $invoice->pendaftaran->paketUmroh->nama_paket }}</div><div>{{ $invoice->pendaftaran->paketUmroh->kode_paket }} - {{ $invoice->pendaftaran->paketUmroh->tanggal_keberangkatan->format('d M Y') }}</div><div>Kode pendaftaran: {{ $invoice->pendaftaran->kode_pendaftaran }}</div></div></div>
</div>
<h5 class="fw-bold">Data Jamaah</h5>
<div class="table-responsive mb-4"><table class="table table-bordered">
    <thead class="table-light"><tr><th>No.</th><th>Nama Lengkap</th><th>NIK</th><th>No. Paspor</th></tr></thead>
    <tbody>@forelse($invoice->pendaftaran->jamaahs as $jamaah)<tr><td>{{ $loop->iteration }}</td><td>{{ $jamaah->nama_lengkap }}</td><td>{{ $jamaah->nik }}</td><td>{{ $jamaah->no_paspor ?: '-' }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">Data jamaah belum diisi.</td></tr>@endforelse</tbody>
</table></div>
<h5 class="fw-bold">Rincian Pembayaran</h5>
<div class="table-responsive mb-4"><table class="table table-bordered">
    <thead class="table-light"><tr><th>Jenis</th><th>Nominal</th><th>Status Pembayaran</th><th>Tanggal Verifikasi</th></tr></thead>
    <tbody>
        @foreach($invoice->pendaftaran->pembayarans->sortBy(fn ($item) => $item->jenis_pembayaran === 'DP' ? 0 : 1) as $pembayaran)
            <tr><td>{{ $pembayaran->jenis_pembayaran }}</td><td>Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</td><td>{{ $pembayaran->status_pembayaran }}</td><td>{{ $pembayaran->verified_at?->format('d M Y H:i') ?: '-' }}</td></tr>
        @endforeach
        @foreach($invoice->pendaftaran->cicilans->sortBy('cicilan_ke') as $cicilan)
            <tr><td>{{ $cicilan->label() }}</td><td>Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}</td><td>{{ $cicilan->status }}</td><td>{{ $cicilan->verified_at?->format('d M Y H:i') ?: '-' }}</td></tr>
        @endforeach
    </tbody>
</table></div>
<div class="row justify-content-end"><div class="col-md-5"><table class="table table-bordered">
    <tr><th>Total Tagihan</th><td class="text-end">Rp {{ number_format($invoice->total_tagihan, 0, ',', '.') }}</td></tr>
    <tr><th>Total Dibayar</th><td class="text-end">Rp {{ number_format($invoice->total_dibayar, 0, ',', '.') }}</td></tr>
    <tr><th>Status Invoice</th><td class="text-end fw-bold">{{ $invoice->status_invoice }}</td></tr>
    <tr><th>Status Pendaftaran</th><td class="text-end">{{ $invoice->pendaftaran->status_pendaftaran }}</td></tr>
</table></div></div>
