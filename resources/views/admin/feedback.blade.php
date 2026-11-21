@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a><a class="nav-link" href="{{ route('admin.reports.index') }}"><i class="bi bi-file-earmark-text me-2"></i>Reports</a><a class="nav-link" href="{{ route('admin.feedback') }}"><i class="bi bi-star me-2"></i>Feedback</a>
@endsection
@section('content')
<h1 class="h3 text-navy mb-4">Citizen Feedback</h1><div class="card border-0 shadow-sm"><div class="list-group list-group-flush">@forelse($feedback as $item)<div class="list-group-item"><div class="d-flex justify-content-between"><strong>{{ $item->report->title }}</strong><span>{{ $item->rating }}/5</span></div><p class="mb-1">{{ $item->comment ?: 'No comment provided.' }}</p><small class="text-muted">{{ $item->citizen->name }}</small></div>@empty<div class="list-group-item text-muted">No feedback yet.</div>@endforelse</div></div><div class="mt-3">{{ $feedback->links() }}</div>
@endsection
