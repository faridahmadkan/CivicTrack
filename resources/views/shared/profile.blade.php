@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a class="nav-link active" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a>
@endsection
@section('content')
<h1 class="h3 text-navy mb-4">My Profile</h1>
<div class="card border-0 shadow-sm col-lg-8"><div class="card-body p-4">
<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Name</label><input name="name" value="{{ old('name',$user->name) }}" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Email</label><input value="{{ $user->email }}" class="form-control" disabled></div>
<div class="mb-3"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone',$user->phone) }}" class="form-control"></div>
<div class="mb-3"><label class="form-label">Profile Photo</label><input type="file" name="profile_photo" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
<div class="mb-3"><label class="form-label">New Password (optional)</label><input type="password" name="password" class="form-control"></div>
<div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="password_confirmation" class="form-control"></div>
<button class="btn btn-emerald">Save Changes</button>
</form></div></div>
@endsection
