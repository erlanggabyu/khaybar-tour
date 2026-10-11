@extends('layouts.app')

@section('title', 'Masuk - Login')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card p-4">
            <div class="card-body">
                <h3 class="card-title text-center mb-4 fw-bold">Sign In</h3>
                <p class="text-muted text-center mb-4">Silakan masuk menggunakan email dan password akun Anda.</p>

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">Alamat Email</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="contoh: budi@gmail.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label fw-medium">Kata Sandi</label>
                        </div>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Masukkan kata sandi Anda">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg py-2">Masuk</button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <span class="text-muted">Belum punya akun?</span>
                    <a href="{{ route('register') }}" class="fw-bold text-decoration-none">Daftar Sekarang</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
