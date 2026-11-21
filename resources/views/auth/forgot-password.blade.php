@extends('layouts.public')
@section('content')
<main class="container py-5"><div class="row justify-content-center"><div class="col-md-5">
<div class="card border-0 shadow-sm"><div class="card-body p-4"><h2 class="fw-bold text-navy mb-3">Reset Password</h2>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.email') }}">@csrf
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" required></div>
<button class="btn btn-emerald w-100">Send Reset Link</button>
</form></div></div>
</div></div></main>
@endsection
