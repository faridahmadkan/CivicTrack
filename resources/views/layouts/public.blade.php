<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#142b22">
<title>{{ $title ?? 'CivicTrack — Better communities, together' }}</title>
<meta name="description" content="Report local issues, follow their progress, and help make your community better with CivicTrack.">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])</head><body>
<nav class="navbar navbar-expand-lg navbar-dark public-navbar sticky-top"><div class="container py-1">
<a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('home') }}"><span class="brand-mark"><i class="bi bi-shield-check"></i></span>CivicTrack</a>
<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="publicNav"><ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
<li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li><li class="nav-item"><a class="nav-link" href="{{ route('how-it-works') }}">How it works</a></li><li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
@auth<li class="nav-item ms-lg-3"><a class="btn btn-light text-navy px-3" href="{{ route('dashboard') }}">Open workspace <i class="bi bi-arrow-up-right ms-1"></i></a></li>
@else<li class="nav-item ms-lg-3"><a class="nav-link px-lg-3" href="{{ route('login') }}">Sign in</a></li><li class="nav-item"><a class="btn btn-emerald px-3" href="{{ route('register') }}">Report an issue <i class="bi bi-arrow-up-right ms-1"></i></a></li>@endauth
</ul></div></div></nav>
@if(session('status'))<div class="container mt-3"><div class="alert alert-success">{{ session('status') }}</div></div>@endif
@yield('content')
<footer class="public-footer mt-5 py-5"><div class="container"><div class="row g-4 align-items-start">
<div class="col-lg-5"><a href="{{ route('home') }}" class="d-inline-flex align-items-center text-white fw-bold fs-5 mb-3"><span class="brand-mark"><i class="bi bi-shield-check"></i></span>CivicTrack</a><p class="mb-0" style="max-width:390px;color:#a9bdb1">A clearer way for communities to report local issues, follow progress, and stay connected to the work being done.</p></div>
<div class="col-6 col-lg-3"><div class="text-uppercase small fw-bold mb-3" style="letter-spacing:.1em;color:#8fa99a">Explore</div><div class="d-flex flex-column gap-2"><a href="{{ route('about') }}">About CivicTrack</a><a href="{{ route('how-it-works') }}">How it works</a><a href="{{ route('contact') }}">Contact</a></div></div>
<div class="col-6 col-lg-4"><div class="text-uppercase small fw-bold mb-3" style="letter-spacing:.1em;color:#8fa99a">Get started</div><div class="d-flex flex-column gap-2"><a href="{{ route('register') }}">Create a citizen account</a><a href="{{ route('login') }}">Sign in to your account</a></div></div>
</div><hr class="my-4" style="border-color:rgba(255,255,255,.13)"><div class="d-flex flex-column flex-md-row justify-content-between gap-2 small" style="color:#91a99b"><span>© {{ date('Y') }} CivicTrack. Community reporting and accountability.</span><span>Built for clearer communication and stronger communities.</span></div></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>