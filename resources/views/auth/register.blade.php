@extends('layouts.public')
@section('content')
<main class="container py-5">
    <div class="row justify-content-center"><div class="col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <h2 class="fw-bold text-navy mb-4">Create a Citizen Account</h2>
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="POST" action="{{ route('register') }}">@csrf
                <div class="mb-3"><label class="form-label">Full name</label><input name="name" value="{{ old('name') }}" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone') }}" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Confirm password</label><input type="password" name="password_confirmation" class="form-control" required></div>
                <button class="btn btn-emerald w-100">Create Account</button>
            </form>
            <p class="text-center mt-3 mb-0 small">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
        </div></div>
    </div></div>
</main>
@endsection
