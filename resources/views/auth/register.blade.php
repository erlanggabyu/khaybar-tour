@extends('layouts.app')

@section('title', 'Pendaftaran Akun')

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-md-7">
        <div class="card p-4 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-center mb-4 fw-bold">Registrasi Akun Baru</h3>

                <form method="POST" action="{{ route('register.post') }}">
                    @csrf

                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">Info Akun Utama</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-medium">Nama Lengkap</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Contoh: Ahmad Fauzi">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-medium">Alamat Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="Contoh: ahmad@gmail.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-medium">Kata Sandi (Min. 8 karakter)</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Buat kata sandi aman">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label fw-medium">Konfirmasi Kata Sandi</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Ulangi kata sandi">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="no_hp" class="form-label fw-medium">No. Handphone (WhatsApp)</label>
                            <input type="text" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" required placeholder="Contoh: 081234567890">
                            @error('no_hp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg py-2">Daftar Akun Baru</button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <span class="text-muted">Sudah memiliki akun?</span>
                    <a href="{{ route('login') }}" class="fw-bold text-decoration-none">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
