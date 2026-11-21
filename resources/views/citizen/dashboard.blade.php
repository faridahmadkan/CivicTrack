@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link active" href="{{ route('citizen.dashboard') }}"><i class="bi bi-grid"></i>Overview</a>
<a class="nav-link" href="{{ route('citizen.reports.index') }}"><i class="bi bi-file-earmark-text"></i>My reports</a>
<a class="nav-link" href="{{ route('citizen.reports.create') }}"><i class="bi bi-plus-circle"></i>Submit a report</a>
@endsection
@section('content')
<div class="page-heading"><div><div class="section-eyebrow mb-2">YOUR COMMUNITY</div><h1>Welcome back, {{ auth()->user()->name }}</h1><p>Keep an eye on the issues you have reported and their latest updates.</p></div><a href="{{ route('citizen.reports.create') }}" class="btn btn-emerald"><i class="bi bi-plus-lg me-2"></i>Submit a report</a></div>
<div class="row g-3 mb-4">
@foreach(['total'=>'Total reports','pending'=>'Pending review','approved'=>'Approved','in_progress'=>'In progress','resolved'=>'Resolved'] as $key=>$label)
<div class="col-6 col-md"><div class="card stat-card h-100"><div class="card-body"><small>{{ $label }}</small><h3>{{ number_format($stats[$key]) }}</h3></div></div></div>
@endforeach
</div>
<div class="row g-4"><div class="col-xl-8"><div class="card h-100"><div class="card-header bg-white d-flex justify-content-between align-items-center gap-2"><div><h2 class="panel-title">Your recent reports</h2><div class="panel-subtitle">Track the latest activity on your submissions</div></div><a href="{{ route('citizen.reports.index') }}" class="btn btn-sm btn-outline-secondary">View all</a></div>
<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Reference</th><th>Issue</th><th>Status</th><th class="text-end"> </th></tr></thead><tbody>
@forelse($recentReports as $report)<tr><td><span class="fw-semibold small">{{ $report->reference_number }}</span></td><td><span class="fw-semibold">{{ $report->title }}</span></td><td><span class="badge text-bg-secondary status-badge">{{ str_replace('_',' ', $report->status) }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('citizen.reports.show',$report) }}">Track <i class="bi bi-arrow-right ms-1"></i></a></td></tr>
@empty<tr><td colspan="4"><div class="empty-state"><i class="bi bi-clipboard2-plus"></i><div class="fw-semibold">Your first report starts here</div><div class="small mb-3">Submit a community issue and follow its progress from this workspace.</div><a href="{{ route('citizen.reports.create') }}" class="btn btn-sm btn-emerald">Create a report</a></div></td></tr>@endforelse
</tbody></table></div></div></div>
<div class="col-xl-4"><div class="card h-100"><div class="card-header bg-white"><h2 class="panel-title">Latest notifications</h2><div class="panel-subtitle">Updates about your reports</div></div><div class="list-group list-group-flush">
@forelse($notifications as $notification)<div class="list-group-item px-4 py-3 border-0 border-bottom"><div class="d-flex gap-2"><span class="stat-icon flex-shrink-0"><i class="bi bi-bell"></i></span><small class="pt-1">{{ $notification->data['message'] }}</small></div></div>
@empty<div class="empty-state"><i class="bi bi-bell-slash"></i><div class="fw-semibold">All caught up</div><div class="small">New updates will appear here.</div></div>@endforelse
</div></div></div></div>
@endsection