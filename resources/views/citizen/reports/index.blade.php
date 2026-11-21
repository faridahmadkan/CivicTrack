@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link" href="{{ route('citizen.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a class="nav-link active" href="{{ route('citizen.reports.index') }}"><i class="bi bi-file-earmark-text me-2"></i>My Reports</a>
<a class="nav-link" href="{{ route('citizen.reports.create') }}"><i class="bi bi-plus-circle me-2"></i>Submit Report</a>
@endsection
@section('content')
<div class="d-flex justify-content-between mb-4"><h1 class="h3 text-navy">My Reports</h1><a href="{{ route('citizen.reports.create') }}" class="btn btn-emerald">Submit Report</a></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Reference</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($reports as $report)<tr><td>{{ $report->reference_number }}</td><td>{{ $report->title }}</td><td>{{ $report->category->name }}</td><td>{{ ucfirst($report->priority) }}</td><td><span class="badge text-bg-secondary">{{ str_replace('_',' ', $report->status) }}</span></td><td><a class="btn btn-sm btn-outline-secondary" href="{{ route('citizen.reports.show',$report) }}">View</a></td></tr>@endforeach
</tbody></table></div></div><div class="mt-3">{{ $reports->links() }}</div>
@endsection
