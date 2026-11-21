@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a class="nav-link active" href="{{ route('admin.reports.index') }}"><i class="bi bi-file-earmark-text me-2"></i>Reports</a>
<a class="nav-link" href="{{ route('admin.categories') }}"><i class="bi bi-tags me-2"></i>Categories</a>
<a class="nav-link" href="{{ route('admin.departments') }}"><i class="bi bi-building me-2"></i>Departments</a>
<a class="nav-link" href="{{ route('admin.users') }}"><i class="bi bi-people me-2"></i>Users</a>
@endsection
@section('content')
<h1 class="h3 text-navy mb-4">All Reports</h1>
<form class="card border-0 shadow-sm p-3 mb-3" method="GET"><div class="row g-2">
<div class="col-md-4"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Title or reference"></div>
<div class="col-md-3"><select name="category_id" class="form-select"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id')==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
<div class="col-md-2"><select name="status" class="form-select"><option value="">All statuses</option>@foreach(['pending_review','approved','assigned','in_progress','resolved','rejected','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')==$status)>{{ str_replace('_',' ',ucfirst($status)) }}</option>@endforeach</select></div>
<div class="col-md-2"><select name="priority" class="form-select"><option value="">Priority</option>@foreach(['low','medium','high','urgent'] as $priority)<option value="{{ $priority }}" @selected(request('priority')==$priority)>{{ ucfirst($priority) }}</option>@endforeach</select></div>
<div class="col-md-1"><button class="btn btn-emerald w-100">Go</button></div>
</div></form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Reference</th><th>Title</th><th>Citizen</th><th>Priority</th><th>Status</th><th></th></tr></thead><tbody>@foreach($reports as $report)<tr><td>{{ $report->reference_number }}</td><td>{{ $report->title }}</td><td>{{ $report->citizen->name }}</td><td>{{ ucfirst($report->priority) }}</td><td><span class="badge text-bg-secondary">{{ str_replace('_',' ', $report->status) }}</span></td><td><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.reports.show',$report) }}">Manage</a></td></tr>@endforeach</tbody></table></div></div><div class="mt-3">{{ $reports->links() }}</div>
@endsection
