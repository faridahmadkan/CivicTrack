@extends('layouts.public')
@section('content')
<main class="container py-5">
    <div class="row justify-content-center"><div class="col-md-5">
        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <h2 class="fw-bold text-navy mb-4">Log in</h2>
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            <form method="POST" action="{{ route('login') }}">@csrf
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Remember me</label></div>
                <button class="btn btn-emerald w-100">Log in</button>
            </form>
            <div class="d-flex justify-content-between mt-3 small"><a href="{{ route('password.request') }}">Forgot password?</a><a href="{{ route('register') }}">Create account</a></div>
        </div></div>
    </div></div>
</main>
@endsection
