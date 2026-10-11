@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $jamaah?->nama_lengkap) }}" required>
    </div>
    <div class="col-md-6">
        <label for="nik" class="form-label">NIK</label>
        <input type="text" inputmode="numeric" maxlength="16" class="form-control" id="nik" name="nik" value="{{ old('nik', $jamaah?->nik) }}" required placeholder="16 digit NIK">
        <div class="form-text">NIK unik untuk setiap jamaah dalam satu pendaftaran.</div>
    </div>
    <div class="col-md-6">
        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
            <option value="">Pilih jenis kelamin</option>
            <option value="L" @selected(old('jenis_kelamin', $jamaah?->jenis_kelamin) === 'L')>Laki-laki</option>
            <option value="P" @selected(old('jenis_kelamin', $jamaah?->jenis_kelamin) === 'P')>Perempuan</option>
        </select>
    </div>
    <div class="col-md-6">
        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $jamaah?->tanggal_lahir?->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-6">
        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $jamaah?->tempat_lahir) }}">
    </div>
    <div class="col-md-6">
        <label for="no_hp" class="form-label">No. HP</label>
        <input type="text" inputmode="numeric" class="form-control" id="no_hp" name="no_hp" value="{{ old('no_hp', $jamaah?->no_hp) }}" required>
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email <span class="text-muted">(opsional)</span></label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $jamaah?->email) }}">
    </div>
    <div class="col-12">
        <label for="alamat" class="form-label">Alamat</label>
        <textarea class="form-control" id="alamat" name="alamat" rows="3" required>{{ old('alamat', $jamaah?->alamat) }}</textarea>
    </div>
</div>

<h5 class="fw-bold mt-4">Data Paspor <span class="text-muted fs-6 fw-normal">(opsional)</span></h5>
<div class="row g-3">
    <div class="col-md-4">
        <label for="no_paspor" class="form-label">No. Paspor</label>
        <input type="text" class="form-control" id="no_paspor" name="no_paspor" value="{{ old('no_paspor', $jamaah?->no_paspor) }}">
    </div>
    <div class="col-md-4">
        <label for="tanggal_terbit_paspor" class="form-label">Tanggal Terbit</label>
        <input type="date" class="form-control" id="tanggal_terbit_paspor" name="tanggal_terbit_paspor" value="{{ old('tanggal_terbit_paspor', $jamaah?->tanggal_terbit_paspor?->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4">
        <label for="tanggal_berakhir_paspor" class="form-label">Tanggal Berakhir</label>
        <input type="date" class="form-control" id="tanggal_berakhir_paspor" name="tanggal_berakhir_paspor" value="{{ old('tanggal_berakhir_paspor', $jamaah?->tanggal_berakhir_paspor?->format('Y-m-d')) }}">
    </div>
</div>

<button type="submit" class="btn btn-primary mt-4">{{ $submitLabel }}</button>
