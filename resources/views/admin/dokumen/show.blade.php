@extends('layouts.app')

@section('title', 'Periksa Dokumen')

@section('content')
<a href="{{ route('admin.dokumen.index') }}" class="text-decoration-none">&larr; Kembali ke daftar dokumen</a>
<div class="row g-4 mt-1">
    <div class="col-lg-7">
        <div class="card"><div class="card-body p-4">
            <h2 class="fw-bold mb-1">{{ $dokumen->jenis_dokumen }}</h2>
            <p class="text-muted">{{ $dokumen->jamaah->nama_lengkap }} - {{ $dokumen->jamaah->pendaftaran->user->name }}</p>
            <dl class="row mb-0">
                <dt class="col-sm-4">Nama File</dt><dd class="col-sm-8">{{ $dokumen->nama_file }}</dd>
                <dt class="col-sm-4">Tipe File</dt><dd class="col-sm-8">{{ $dokumen->mime_type }}</dd>
                <dt class="col-sm-4">Ukuran</dt><dd class="col-sm-8">{{ number_format($dokumen->ukuran_file / 1024, 1, ',', '.') }} KB</dd>
                <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ $dokumen->status_dokumen }}</dd>
                <dt class="col-sm-4">Catatan Admin</dt><dd class="col-sm-8">{{ $dokumen->catatan_admin ?: '-' }}</dd>
            </dl>
            <a href="{{ route('dokumen.download', $dokumen) }}" class="btn btn-outline-primary mt-3">Unduh File</a>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card"><div class="card-body p-4">
            <h4 class="fw-bold mb-3">Verifikasi Dokumen</h4>
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('admin.dokumen.verify', $dokumen) }}">
                @csrf
                <div class="mb-3">
                    <label for="status_dokumen" class="form-label">Status</label>
                    <select name="status_dokumen" id="status_dokumen" class="form-select" required>
                        @foreach(\App\Models\Dokumen::verificationStatuses() as $status)
                            <option value="{{ $status }}" @selected(old('status_dokumen', $dokumen->status_dokumen) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="catatan_admin" class="form-label">Catatan Revisi/Admin</label>
                    <textarea name="catatan_admin" id="catatan_admin" rows="5" class="form-control">{{ old('catatan_admin', $dokumen->catatan_admin) }}</textarea>
                    <small class="text-muted">Wajib diisi untuk status Tidak Valid atau Perlu Revisi.</small>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Verifikasi</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
