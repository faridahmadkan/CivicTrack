@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link" href="{{ route('citizen.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a class="nav-link active" href="{{ route('citizen.reports.index') }}"><i class="bi bi-file-earmark-text me-2"></i>My Reports</a>
<a class="nav-link" href="{{ route('citizen.reports.create') }}"><i class="bi bi-plus-circle me-2"></i>Submit Report</a>
@endsection
@section('content')
<div class="d-flex justify-content-between mb-4"><div><h1 class="h3 text-navy mb-1">{{ $report->title }}</h1><span class="text-muted">{{ $report->reference_number }}</span></div><span class="badge text-bg-secondary align-self-start">{{ str_replace('_',' ', $report->status) }}</span></div>
<div class="row g-4"><div class="col-lg-8">
<div class="card border-0 shadow-sm mb-4"><div class="card-body"><h2 class="h6">Report Details</h2><p>{{ $report->description }}</p><dl class="row mb-0"><dt class="col-sm-3">Location</dt><dd class="col-sm-9">{{ $report->location }}</dd><dt class="col-sm-3">Category</dt><dd class="col-sm-9">{{ $report->category->name }}</dd><dt class="col-sm-3">Priority</dt><dd class="col-sm-9">{{ ucfirst($report->priority) }}</dd></dl></div></div>
<div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white"><h2 class="h6 mb-0">Status History</h2></div><div class="list-group list-group-flush">@foreach($report->statusHistories as $history)<div class="list-group-item"><strong>{{ str_replace('_',' ', $history->new_status) }}</strong><small class="d-block text-muted">{{ $history->created_at->diffForHumans() }} — {{ $history->notes }}</small></div>@endforeach</div></div>
<div class="card border-0 shadow-sm"><div class="card-header bg-white"><h2 class="h6 mb-0">Progress Updates</h2></div><div class="list-group list-group-flush">@forelse($report->progressUpdates->where('is_internal',false) as $update)<div class="list-group-item"><p class="mb-1">{{ $update->description }}</p><small class="text-muted">{{ $update->created_at->diffForHumans() }}</small></div>@empty<div class="list-group-item text-muted">No public progress updates yet.</div>@endforelse</div></div>
</div><div class="col-lg-4">
@if(in_array($report->status,['pending_review','approved']))<form method="POST" action="{{ route('citizen.reports.cancel',$report) }}" class="mb-3">@csrf<button class="btn btn-outline-danger w-100">Cancel Report</button></form>@endif
@if($report->status === 'resolved' && !$report->feedback)<div class="card border-0 shadow-sm"><div class="card-body"><h2 class="h6">Rate this resolution</h2><form method="POST" action="{{ route('citizen.reports.feedback',$report) }}">@csrf<select name="rating" class="form-select mb-2" required>@for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }} star{{ $i>1?'s':'' }}</option>@endfor</select><textarea name="comment" class="form-control mb-2" rows="3" placeholder="Optional feedback"></textarea><button class="btn btn-emerald w-100">Submit Feedback</button></form></div></div>@elseif($report->feedback)<div class="alert alert-success">Your feedback: {{ $report->feedback->rating }}/5</div>@endif
</div></div>
@endsection
