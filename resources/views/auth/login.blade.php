@extends('layouts.public')
@section('content')
<section class="auth-wrap"><div class="container"><div class="auth-card">
<div class="text-center mb-4"><span class="brand-mark mb-3" style="width:52px;height:52px;font-size:1.35rem"><i class="bi bi-shield-check"></i></span><div class="auth-kicker mb-2">WELCOME BACK</div><h1 class="h2 fw-bold mb-2">Sign in to CivicTrack</h1><p class="text-secondary mb-0">Access your reports, updates, and workspace.</p></div>
<div class="card"><div class="card-body">
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<form method="POST" action="{{ route('login') }}">@csrf
<div class="mb-3"><label class="form-label" for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="you@example.com" autocomplete="email" required autofocus></div>
<div class="mb-3"><div class="d-flex justify-content-between"><label class="form-label" for="password">Password</label><a class="small" href="{{ route('password.request') }}">Forgot password?</a></div><input id="password" type="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required></div>
<div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label small text-secondary" for="remember">Keep me signed in</label></div>
<button class="btn btn-emerald w-100 py-3">Sign in <i class="bi bi-arrow-right ms-2"></i></button>
</form></div></div>
<p class="text-center text-secondary mt-4 small">New to CivicTrack? <a class="fw-bold" href="{{ route('register') }}">Create a citizen account</a></p>
</div></div></section>
@endsection