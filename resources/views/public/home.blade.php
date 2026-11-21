@extends('layouts.public')
@section('content')
<header class="hero-section text-white">
    <div class="container py-5">
        <div class="row align-items-center min-vh-50 py-5">
            <div class="col-lg-7">
                <span class="badge bg-emerald mb-3">Community-powered accountability</span>
                <h1 class="display-4 fw-bold">Make your community better, one report at a time.</h1>
                <p class="lead my-4">Report local issues, track progress, and stay informed as civic teams resolve problems.</p>
                <a href="{{ route('register') }}" class="btn btn-emerald btn-lg me-2">Submit a Report</a>
                <a href="{{ route('how-it-works') }}" class="btn btn-outline-light btn-lg">See How It Works</a>
            </div>
        </div>
    </div>
</header>
<section class="py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="text-navy">{{ number_format($stats['reports']) }}</h2><p class="mb-0">Community reports</p></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="text-navy">{{ number_format($stats['resolved']) }}</h2><p class="mb-0">Issues resolved</p></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="text-navy">{{ number_format($stats['citizens']) }}</h2><p class="mb-0">Registered citizens</p></div></div></div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold text-navy mb-4">What can you report?</h2>
        <div class="row g-3">
            @foreach($categories as $category)
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body"><i class="bi bi-pin-map text-emerald fs-3"></i><h6 class="fw-bold mt-3 mb-1">{{ $category->name }}</h6><p class="small text-muted mb-0">{{ $category->description }}</p></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
