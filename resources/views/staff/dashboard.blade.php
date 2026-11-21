@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link active" href="{{ route('staff.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a class="nav-link" href="{{ route('staff.assignments.index') }}"><i class="bi bi-clipboard-check me-2"></i>Assignments</a>
@endsection
@section('content')
<h1 class="h3 text-navy mb-4">Staff Dashboard</h1><div class="row g-3 mb-4">
@foreach(['total'=>'Total Assigned','pending'=>'Pending','progress'=>'In Progress','resolved'=>'Resolved'] as $key=>$label)<div class="col-sm-6 col-lg-3"><div class="card stat-card h-100"><div class="card-body"><small class="text-muted">{{ $label }}</small><h3 class="mb-0">{{ $stats[$key] }}</h3></div></div></div>@endforeach
</div>
<div class="card border-0 shadow-sm"><div class="card-header bg-white"><h2 class="h6 mb-0">Recent Assignments</h2></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Reference</th><th>Title</th><th>Status</th><th></th></tr></thead><tbody>@foreach($recentAssignments as $assignment)<tr><td>{{ $assignment->report->reference_number }}</td><td>{{ $assignment->report->title }}</td><td>{{ str_replace('_',' ', $assignment->status) }}</td><td><a class="btn btn-sm btn-outline-secondary" href="{{ route('staff.assignments.show',$assignment) }}">Open</a></td></tr>@endforeach</tbody></table></div></div>
@endsection
