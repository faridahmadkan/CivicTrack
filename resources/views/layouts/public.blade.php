<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'CivicTrack' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-navy sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}"><i class="bi bi-shield-check me-2"></i>CivicTrack</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('how-it-works') }}">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                @auth
                    <li class="nav-item ms-lg-2"><a class="btn btn-light text-navy" href="{{ route('dashboard') }}">Dashboard</a></li>
                @else
                    <li class="nav-item ms-lg-2"><a class="btn btn-outline-light me-2" href="{{ route('login') }}">Log in</a></li>
                    <li class="nav-item"><a class="btn btn-emerald" href="{{ route('register') }}">Report an Issue</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
@yield('content')
<footer class="bg-navy text-white py-4 mt-5">
    <div class="container d-flex flex-column flex-md-row justify-content-between">
        <span>&copy; {{ date('Y') }} CivicTrack</span>
        <span>Community issue reporting and accountability</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
