@extends('layouts.public')
@section('content')
<main class="container py-5"><div class="row justify-content-center"><div class="col-md-5">
<div class="card border-0 shadow-sm"><div class="card-body p-4"><h2 class="fw-bold text-navy mb-3">Choose a New Password</h2>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.update') }}">@csrf
<input type="hidden" name="token" value="{{ $token }}">
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ $email }}" class="form-control" required></div>
<div class="mb-3"><label class="form-label">New Password</label><input type="password" name="password" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
<button class="btn btn-emerald w-100">Reset Password</button>
</form></div></div>
</div></div></main>
@endsection
