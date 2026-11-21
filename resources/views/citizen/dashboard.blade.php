@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link active" href="{{ route('citizen.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a class="nav-link" href="{{ route('citizen.reports.index') }}"><i class="bi bi-file-earmark-text me-2"></i>My Reports</a>
<a class="nav-link" href="{{ route('citizen.reports.create') }}"><i class="bi bi-plus-circle me-2"></i>Submit Report</a>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="h3 text-navy mb-1">Citizen Dashboard</h1><p class="text-muted mb-0">Track your community reports</p></div><a href="{{ route('citizen.reports.create') }}" class="btn btn-emerald"><i class="bi bi-plus-lg me-1"></i>New Report</a></div>
<div class="row g-3 mb-4">
@foreach(['total'=>'Total Reports','pending'=>'Pending Review','approved'=>'Approved','in_progress'=>'In Progress','resolved'=>'Resolved'] as $key=>$label)
<div class="col-sm-6 col-xl"><div class="card stat-card h-100"><div class="card-body"><small class="text-muted">{{ $label }}</small><h3 class="mb-0">{{ $stats[$key] }}</h3></div></div></div>
@endforeach
</div>
<div class="row g-4"><div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-header bg-white"><h2 class="h6 mb-0">Recent Reports</h2></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Reference</th><th>Title</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($recentReports as $report)<tr><td>{{ $report->reference_number }}</td><td>{{ $report->title }}</td><td><span class="badge text-bg-secondary status-badge">{{ str_replace('_',' ', $report->status) }}</span></td><td><a class="btn btn-sm btn-outline-secondary" href="{{ route('citizen.reports.show',$report) }}">View</a></td></tr>@endforeach
</tbody></table></div></div></div>
<div class="col-lg-4"><div class="card border-0 shadow-sm"><div class="card-header bg-white"><h2 class="h6 mb-0">Latest Notifications</h2></div><div class="list-group list-group-flush">@forelse($notifications as $notification)<div class="list-group-item"><small>{{ $notification->data['message'] }}</small></div>@empty<div class="list-group-item text-muted">No notifications.</div>@endforelse</div></div></div></div>
@endsection
