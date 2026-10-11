@extends('layouts.app')

@section('title', 'Upload Bukti Cicilan')

@section('content')
<a href="{{ route('pendaftaran.pembayaran.index', $cicilan->pendaftaran) }}" class="text-decoration-none">&larr; Kembali ke tagihan</a>
<div class="row justify-content-center mt-3"><div class="col-lg-7">
    <div class="card"><div class="card-body p-4">
        <h2 class="fw-bold mb-1">Upload Bukti {{ $cicilan->label() }}</h2>
        <p class="text-muted">Nominal: Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}</p>
        <div class="alert alert-info">File yang diterima: PDF, JPG, JPEG, atau PNG. Maksimal 2 MB.</div>
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('cicilan.store', $cicilan) }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="bukti_file" class="form-label">Bukti Cicilan</label>
                <input type="file" name="bukti_file" id="bukti_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload Bukti Cicilan</button>
        </form>
    </div></div>
</div></div>
@endsection
