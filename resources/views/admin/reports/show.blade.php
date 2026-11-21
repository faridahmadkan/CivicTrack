@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a><a class="nav-link active" href="{{ route('admin.reports.index') }}"><i class="bi bi-file-earmark-text me-2"></i>Reports</a>
@endsection
@section('content')
<h1 class="h3 text-navy">{{ $report->title }}</h1><p class="text-muted">{{ $report->reference_number }}</p>
<div class="row g-4"><div class="col-lg-8">
<div class="card border-0 shadow-sm mb-4"><div class="card-body"><h2 class="h6">Description</h2><p>{{ $report->description }}</p><dl class="row mb-0"><dt class="col-sm-3">Citizen</dt><dd class="col-sm-9">{{ $report->citizen->name }}</dd><dt class="col-sm-3">Location</dt><dd class="col-sm-9">{{ $report->location }}</dd><dt class="col-sm-3">Category</dt><dd class="col-sm-9">{{ $report->category->name }}</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ str_replace('_',' ', $report->status) }}</dd></dl></div></div>
<div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white"><h2 class="h6 mb-0">Status History</h2></div><div class="list-group list-group-flush">@foreach($report->statusHistories as $history)<div class="list-group-item"><strong>{{ str_replace('_',' ', $history->new_status) }}</strong><small class="d-block text-muted">{{ $history->created_at->diffForHumans() }} — {{ $history->notes }}</small></div>@endforeach</div></div>
</div><div class="col-lg-4">
@if($report->status === 'pending_review')
<div class="card border-0 shadow-sm mb-3"><div class="card-body"><form method="POST" action="{{ route('admin.reports.approve',$report) }}">@csrf<button class="btn btn-emerald w-100">Approve Report</button></form></div></div>
<div class="card border-0 shadow-sm mb-3"><div class="card-body"><h2 class="h6">Reject Report</h2><form method="POST" action="{{ route('admin.reports.reject',$report) }}">@csrf<textarea name="rejection_reason" class="form-control mb-2" rows="3" required></textarea><button class="btn btn-outline-danger w-100">Reject</button></form></div></div>
@endif
@if($report->status === 'approved')
<div class="card border-0 shadow-sm mb-3"><div class="card-body"><h2 class="h6">Assign Staff</h2><form method="POST" action="{{ route('admin.reports.assign',$report) }}">@csrf<select name="staff_id" class="form-select mb-2" required><option value="">Select staff</option>@foreach($staff as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach</select><select name="department_id" class="form-select mb-2"><option value="">Department</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select><textarea name="notes" class="form-control mb-2" rows="2" placeholder="Assignment notes"></textarea><button class="btn btn-emerald w-100">Assign Report</button></form></div></div>
@endif
</div></div>
@endsection
