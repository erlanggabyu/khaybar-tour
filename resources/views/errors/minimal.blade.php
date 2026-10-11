<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') - Khaybar Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="card border-0 shadow-sm text-center" style="max-width: 560px;">
            <div class="card-body p-5">
                <div class="display-3 fw-bold text-primary">@yield('code')</div>
                <h1 class="h3 mt-3">@yield('title')</h1>
                <p class="text-muted">@yield('message')</p>
                <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke Beranda</a>
            </div>
        </div>
    </main>
</body>
</html>
