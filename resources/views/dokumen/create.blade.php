@extends('layouts.app')

@section('title', 'Upload Dokumen')

@section('content')
<a href="{{ route('jamaah.dokumen.index', $jamaah) }}" class="text-decoration-none">&larr; Kembali ke daftar dokumen</a>
<div class="row justify-content-center mt-3">
    <div class="col-lg-7">
        <div class="card"><div class="card-body p-4">
            <h2 class="fw-bold mb-1">Upload Dokumen</h2>
            <p class="text-muted">{{ $jamaah->nama_lengkap }}</p>
            <div class="alert alert-info">File yang diterima: PDF, JPG, JPEG, atau PNG. Maksimal 2 MB.</div>

            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('jamaah.dokumen.store', $jamaah) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="jenis_dokumen" class="form-label">Jenis Dokumen</label>
                    <select name="jenis_dokumen" id="jenis_dokumen" class="form-select" required>
                        <option value="">Pilih jenis dokumen</option>
                        @foreach(\App\Models\Dokumen::documentTypes() as $jenis)
                            @php($existing = $jamaah->dokumens->firstWhere('jenis_dokumen', $jenis))
                            @if(!$existing || $existing->canBeReuploaded())
                                <option value="{{ $jenis }}" @selected(old('jenis_dokumen') === $jenis)>{{ $jenis }}{{ $existing ? ' - Upload Ulang' : '' }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="file_dokumen" class="form-label">Pilih File</label>
                    <input type="file" name="file_dokumen" id="file_dokumen" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload Dokumen</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
