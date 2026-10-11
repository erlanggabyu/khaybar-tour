<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Khaybar Tour') - Sistem Pendaftaran Umroh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --khaybar: #0b5d49; --khaybar-dark: #073d31; }
        body { min-height: 100vh; display: flex; flex-direction: column; font-family: Inter, sans-serif; background: #f5f7f9; color: #17212b; }
        main { flex: 1; }
        .navbar { background: linear-gradient(105deg, var(--khaybar-dark), var(--khaybar)) !important; }
        .navbar-brand { font-weight: 700; letter-spacing: .04em; }
        .nav-link.active { color: #fff !important; font-weight: 600; }
        .card { border: 1px solid rgba(15, 23, 42, .07); border-radius: .9rem; box-shadow: 0 .3rem 1rem rgba(15, 23, 42, .06); }
        .table { --bs-table-bg: transparent; }
        .table thead th { white-space: nowrap; color: #52606d; font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; }
        .btn { font-weight: 600; border-radius: .55rem; }
        .btn-primary { --bs-btn-bg: var(--khaybar); --bs-btn-border-color: var(--khaybar); --bs-btn-hover-bg: var(--khaybar-dark); --bs-btn-hover-border-color: var(--khaybar-dark); }
        .text-primary { color: var(--khaybar) !important; }
        .bg-primary { background-color: var(--khaybar) !important; }
        .progress-bar { min-width: 2px; }
        .page-heading { margin-bottom: 1.5rem; }
        footer { background: #fff; }
        @media print { .navbar, footer, .d-print-none { display: none !important; } body { background: #fff; } main { padding: 0 !important; } .card { box-shadow: none; } }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">KHAYBAR TOUR</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Buka navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('pakets.*')) active @endif" href="{{ route('pakets.index') }}">Paket Umroh</a></li>
                    @auth
                        @if(Auth::user()->isAdmin())
                            <li class="nav-item"><a class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle @if(request()->routeIs('admin.*') && !request()->routeIs('admin.dashboard')) active @endif" href="#" role="button" data-bs-toggle="dropdown">Administrasi</a>
                                <ul class="dropdown-menu shadow">
                                    <li><a class="dropdown-item" href="{{ route('admin.pakets.index') }}">Kelola Paket</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pendaftaran.index') }}">Data Pendaftaran</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dokumen.index') }}">Verifikasi Dokumen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pembayaran.index') }}">Verifikasi Pembayaran</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.laporan.pendaftaran') }}">Laporan</a></li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item"><a class="nav-link @if(request()->routeIs('pelanggan.dashboard')) active @endif" href="{{ route('pelanggan.dashboard') }}">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link @if(request()->routeIs('pendaftaran.*') || request()->routeIs('jamaah.*') || request()->routeIs('pembayaran.*') || request()->routeIs('invoice.*')) active @endif" href="{{ route('pendaftaran.index') }}">Pendaftaran Saya</a></li>
                        @endif
                    @endauth
                </ul>
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">{{ Auth::user()->name }} <span class="badge text-bg-light ms-1">{{ ucfirst(Auth::user()->role) }}</span></a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="dropdown-item text-danger">Keluar</button></form></li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Masuk</a></li>
                        <li class="nav-item"><a class="btn btn-light btn-sm ms-lg-2 px-3" href="{{ route('register') }}">Daftar Akun</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
    <main class="py-4 py-lg-5">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
            @endif
            @yield('content')
        </div>
    </main>
    <footer class="border-top py-3 text-center text-muted"><div class="container"><small>&copy; {{ date('Y') }} Khaybar Tour & Travel Umroh</small></div></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
