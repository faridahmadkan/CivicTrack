@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link active" href="{{ route('staff.dashboard') }}"><i class="bi bi-grid"></i>Overview</a>
<a class="nav-link" href="{{ route('staff.assignments.index') }}"><i class="bi bi-clipboard-check"></i>My assignments</a>
@endsection
@section('content')
<div class="page-heading"><div><div class="section-eyebrow mb-2">FIELD OPERATIONS</div><h1>Your work overview</h1><p>Review assigned community issues and keep their progress up to date.</p></div><a href="{{ route('staff.assignments.index') }}" class="btn btn-emerald"><i class="bi bi-list-check me-2"></i>Open assignments</a></div>
<div class="row g-3 mb-4">@foreach(['total'=>'Total assigned','pending'=>'Pending','progress'=>'In progress','resolved'=>'Resolved'] as $key=>$label)<div class="col-6 col-xl-3"><div class="card stat-card h-100"><div class="card-body"><small>{{ $label }}</small><h3>{{ number_format($stats[$key]) }}</h3></div></div></div>@endforeach</div>
<div class="card"><div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h2 class="panel-title">Recent assignments</h2><div class="panel-subtitle">Work items most recently assigned to you</div></div><a href="{{ route('staff.assignments.index') }}" class="btn btn-sm btn-outline-secondary">All assignments <i class="bi bi-arrow-right ms-1"></i></a></div>
<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Reference</th><th>Issue</th><th>Status</th><th class="text-end">Action</th></tr></thead><tbody>
@forelse($recentAssignments as $assignment)<tr><td><span class="fw-semibold small">{{ $assignment->report->reference_number }}</span></td><td><span class="fw-semibold">{{ $assignment->report->title }}</span></td><td><span class="badge text-bg-secondary">{{ str_replace('_',' ', $assignment->status) }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('staff.assignments.show',$assignment) }}">Open task <i class="bi bi-arrow-up-right ms-1"></i></a></td></tr>
@empty<tr><td colspan="4"><div class="empty-state"><i class="bi bi-clipboard-check"></i><div class="fw-semibold">No assignments yet</div><div class="small">New work assigned to you will appear here.</div></div></td></tr>@endforelse
</tbody></table></div></div>
@endsection