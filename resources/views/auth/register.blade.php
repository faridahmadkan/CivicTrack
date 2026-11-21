@extends('layouts.public')
@section('content')
<section class="auth-wrap"><div class="container"><div class="auth-card" style="max-width:540px">
<div class="text-center mb-4"><span class="brand-mark mb-3" style="width:52px;height:52px;font-size:1.35rem"><i class="bi bi-person-plus"></i></span><div class="auth-kicker mb-2">JOIN YOUR COMMUNITY</div><h1 class="h2 fw-bold mb-2">Create your account</h1><p class="text-secondary mb-0">Register to report local issues and follow their progress.</p></div>
<div class="card"><div class="card-body">
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('register') }}">@csrf
<div class="mb-3"><label class="form-label" for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="Your full name" autocomplete="name" required></div>
<div class="mb-3"><label class="form-label" for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="you@example.com" autocomplete="email" required></div>
<div class="mb-3"><label class="form-label" for="phone">Phone number <span class="text-secondary fw-normal">(optional)</span></label><input id="phone" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+93 7X XXX XXXX" autocomplete="tel"></div>
<div class="row g-3"><div class="col-md-6"><label class="form-label" for="password">Password</label><input id="password" type="password" name="password" class="form-control" autocomplete="new-password" required></div><div class="col-md-6"><label class="form-label" for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required></div></div>
<button class="btn btn-emerald w-100 py-3 mt-4">Create citizen account <i class="bi bi-arrow-right ms-2"></i></button>
</form></div></div><p class="text-center text-secondary mt-4 small">Already registered? <a class="fw-bold" href="{{ route('login') }}">Sign in instead</a></p>
</div></div></section>
@endsection