@extends('layouts.app')

@section('title', 'Daftar Paket Umroh')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <a href="{{ route('pakets.show', $paketUmroh) }}" class="text-decoration-none">&larr; Kembali ke detail paket</a>

        <div class="card mt-3">
            <div class="card-body p-4">
                <h2 class="fw-bold mb-1">Form Pendaftaran Paket</h2>
                <p class="text-muted mb-4">{{ $paketUmroh->kode_paket }} - {{ $paketUmroh->nama_paket }}</p>

                <div class="row g-3 bg-light rounded p-3 mb-4">
                    <div class="col-md-4"><small class="text-muted">Harga/Jamaah</small><div class="fw-bold">Rp {{ number_format($paketUmroh->harga, 0, ',', '.') }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Keberangkatan</small><div class="fw-bold">{{ $paketUmroh->tanggal_keberangkatan->format('d M Y') }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Sisa Kuota</small><div class="fw-bold">{{ $paketUmroh->sisa_kuota }} Jamaah</div></div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('pendaftaran.store', $paketUmroh) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="jumlah_jamaah" class="form-label fw-bold">Jumlah Jamaah</label>
                        <input type="number" name="jumlah_jamaah" id="jumlah_jamaah" min="1" max="{{ $paketUmroh->sisa_kuota }}" value="{{ old('jumlah_jamaah', 1) }}" class="form-control @error('jumlah_jamaah') is-invalid @enderror" required>
                        @error('jumlah_jamaah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label for="catatan_pelanggan" class="form-label fw-bold">Catatan Pelanggan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="catatan_pelanggan" id="catatan_pelanggan" rows="4" maxlength="2000" class="form-control @error('catatan_pelanggan') is-invalid @enderror">{{ old('catatan_pelanggan') }}</textarea>
                        @error('catatan_pelanggan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Pendaftaran</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
