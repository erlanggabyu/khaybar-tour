@extends('layouts.app')

@section('title', 'Detail Cicilan')

@section('content')
<a href="{{ route('admin.pembayaran.index') }}" class="text-decoration-none">&larr; Kembali ke daftar pembayaran</a>
<div class="row g-4 mt-1">
    <div class="col-lg-7"><div class="card"><div class="card-body p-4">
        <h2 class="fw-bold mb-1">{{ $cicilan->label() }}</h2>
        <p class="text-muted">{{ $cicilan->pendaftaran->kode_pendaftaran }} - {{ $cicilan->pendaftaran->user->name }}</p>
        <dl class="row mb-0">
            <dt class="col-sm-4">Nominal</dt><dd class="col-sm-8">Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}</dd>
            <dt class="col-sm-4">Jatuh Tempo</dt><dd class="col-sm-8">{{ $cicilan->due_date->format('d M Y') }}</dd>
            <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ $cicilan->status }}</dd>
            <dt class="col-sm-4">Nama File</dt><dd class="col-sm-8">{{ $cicilan->nama_file ?: '-' }}</dd>
            <dt class="col-sm-4">Tipe File</dt><dd class="col-sm-8">{{ $cicilan->mime_type ?: '-' }}</dd>
            <dt class="col-sm-4">Catatan Admin</dt><dd class="col-sm-8">{{ $cicilan->catatan_admin ?: '-' }}</dd>
        </dl>
        @if($cicilan->bukti_file)
            <a href="{{ route('cicilan.download', $cicilan) }}" class="btn btn-outline-primary mt-3">Unduh Bukti Cicilan</a>
        @endif
    </div></div></div>
    <div class="col-lg-5"><div class="card"><div class="card-body p-4">
        <h4 class="fw-bold mb-3">Verifikasi Cicilan</h4>
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if($cicilan->status === 'Menunggu Verifikasi')
            <form method="POST" action="{{ route('admin.cicilan.verify', $cicilan) }}">
                @csrf
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="Valid">Valid</option>
                        <option value="Tidak Valid">Tidak Valid</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="catatan_admin" class="form-label">Catatan Admin</label>
                    <textarea name="catatan_admin" id="catatan_admin" rows="5" class="form-control">{{ old('catatan_admin', $cicilan->catatan_admin) }}</textarea>
                    <small class="text-muted">Wajib diisi jika cicilan Tidak Valid.</small>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Verifikasi</button>
            </form>
        @else
            <p class="text-muted mb-0">Cicilan ini tidak sedang menunggu verifikasi.</p>
        @endif
    </div></div></div>
</div>
@endsection
