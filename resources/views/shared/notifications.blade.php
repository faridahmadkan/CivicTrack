@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 text-navy mb-0">Notifications</h1>
<form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-outline-secondary">Mark all as read</button></form></div>
<div class="card border-0 shadow-sm"><div class="list-group list-group-flush">
@forelse($notifications as $notification)
<a href="{{ route('notifications.read', $notification->id) }}" class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'fw-semibold' }}">
<span>{{ $notification->data['message'] }}</span><small class="d-block text-muted">{{ $notification->created_at->diffForHumans() }}</small>
</a>
@empty
<div class="list-group-item text-muted">No notifications.</div>
@endforelse
</div></div>
<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
