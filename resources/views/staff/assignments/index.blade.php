@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link" href="{{ route('staff.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a><a class="nav-link active" href="{{ route('staff.assignments.index') }}"><i class="bi bi-clipboard-check me-2"></i>Assignments</a>
@endsection
@section('content')
<h1 class="h3 text-navy mb-4">My Assignments</h1><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Reference</th><th>Title</th><th>Report Status</th><th>Assignment Status</th><th></th></tr></thead><tbody>@foreach($assignments as $assignment)<tr><td>{{ $assignment->report->reference_number }}</td><td>{{ $assignment->report->title }}</td><td>{{ str_replace('_',' ', $assignment->report->status) }}</td><td>{{ ucfirst($assignment->status) }}</td><td><a class="btn btn-sm btn-outline-secondary" href="{{ route('staff.assignments.show',$assignment) }}">Open</a></td></tr>@endforeach</tbody></table></div></div><div class="mt-3">{{ $assignments->links() }}</div>
@endsection
