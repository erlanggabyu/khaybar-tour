@php($paket = $paketUmroh ?? null)

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Data belum dapat disimpan.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label for="kode_paket" class="form-label">Kode Paket</label>
        <input type="text" id="kode_paket" name="kode_paket" class="form-control @error('kode_paket') is-invalid @enderror" value="{{ old('kode_paket', $paket?->kode_paket) }}" required maxlength="30">
    </div>
    <div class="col-md-8">
        <label for="nama_paket" class="form-label">Nama Paket</label>
        <input type="text" id="nama_paket" name="nama_paket" class="form-control @error('nama_paket') is-invalid @enderror" value="{{ old('nama_paket', $paket?->nama_paket) }}" required maxlength="255">
    </div>
    <div class="col-md-4">
        <label for="jenis_perjalanan" class="form-label">Jenis Perjalanan</label>
        <input type="text" id="jenis_perjalanan" name="jenis_perjalanan" class="form-control @error('jenis_perjalanan') is-invalid @enderror" value="{{ old('jenis_perjalanan', $paket?->jenis_perjalanan) }}" required maxlength="100" placeholder="Contoh: Reguler">
    </div>
    <div class="col-md-4">
        <label for="durasi" class="form-label">Durasi (Hari)</label>
        <input type="number" id="durasi" name="durasi" class="form-control @error('durasi') is-invalid @enderror" value="{{ old('durasi', $paket?->durasi) }}" required min="1" max="60">
    </div>
    <div class="col-md-4">
        <label for="harga" class="form-label">Harga</label>
        <input type="number" id="harga" name="harga" class="form-control @error('harga') is-invalid @enderror" value="{{ old('harga', $paket?->harga) }}" required min="0" step="1000">
    </div>
    <div class="col-md-4">
        <label for="kota_keberangkatan" class="form-label">Kota Keberangkatan</label>
        <input type="text" id="kota_keberangkatan" name="kota_keberangkatan" class="form-control @error('kota_keberangkatan') is-invalid @enderror" value="{{ old('kota_keberangkatan', $paket?->kota_keberangkatan) }}" required maxlength="100">
    </div>
    <div class="col-md-4">
        <label for="tanggal_keberangkatan" class="form-label">Tanggal Keberangkatan</label>
        <input type="date" id="tanggal_keberangkatan" name="tanggal_keberangkatan" class="form-control @error('tanggal_keberangkatan') is-invalid @enderror" value="{{ old('tanggal_keberangkatan', $paket?->tanggal_keberangkatan?->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-4">
        <label for="status_paket" class="form-label">Status Paket</label>
        <select id="status_paket" name="status_paket" class="form-select @error('status_paket') is-invalid @enderror" required>
            @foreach(\App\Models\PaketUmroh::statuses() as $status)
                <option value="{{ $status }}" @selected(old('status_paket', $paket?->status_paket ?? \App\Models\PaketUmroh::STATUS_TERSEDIA) === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label for="kuota_jamaah" class="form-label">Kuota Jamaah</label>
        <input type="number" id="kuota_jamaah" name="kuota_jamaah" class="form-control @error('kuota_jamaah') is-invalid @enderror" value="{{ old('kuota_jamaah', $paket?->kuota_jamaah) }}" required min="1">
    </div>
    <div class="col-md-6">
        <label for="sisa_kuota" class="form-label">Sisa Kuota</label>
        <input type="number" id="sisa_kuota" name="sisa_kuota" class="form-control @error('sisa_kuota') is-invalid @enderror" value="{{ old('sisa_kuota', $paket?->sisa_kuota) }}" required min="0">
    </div>
    <div class="col-md-4">
        <label for="fasilitas" class="form-label">Fasilitas</label>
        <textarea id="fasilitas" name="fasilitas" rows="7" class="form-control @error('fasilitas') is-invalid @enderror">{{ old('fasilitas', $paket?->fasilitas) }}</textarea>
    </div>
    <div class="col-md-4">
        <label for="jadwal_perjalanan" class="form-label">Jadwal Perjalanan</label>
        <textarea id="jadwal_perjalanan" name="jadwal_perjalanan" rows="7" class="form-control @error('jadwal_perjalanan') is-invalid @enderror">{{ old('jadwal_perjalanan', $paket?->jadwal_perjalanan) }}</textarea>
    </div>
    <div class="col-md-4">
        <label for="persyaratan_dokumen" class="form-label">Persyaratan Dokumen</label>
        <textarea id="persyaratan_dokumen" name="persyaratan_dokumen" rows="7" class="form-control @error('persyaratan_dokumen') is-invalid @enderror">{{ old('persyaratan_dokumen', $paket?->persyaratan_dokumen) }}</textarea>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.pakets.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
