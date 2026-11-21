@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a class="nav-link active" href="{{ route('admin.users') }}"><i class="bi bi-people me-2"></i>Users</a>
@endsection
@section('content')
<h1 class="h3 text-navy mb-4">Create Staff Account</h1>
<div class="card border-0 shadow-sm col-lg-7"><div class="card-body p-4">
<form method="POST" action="{{ route('admin.users.store-staff') }}">@csrf
<div class="mb-3"><label class="form-label">Name</label><input name="name" value="{{ old('name') }}" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone') }}" class="form-control"></div>
<div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
<button class="btn btn-emerald">Create Staff Account</button>
</form></div></div>
@endsection
