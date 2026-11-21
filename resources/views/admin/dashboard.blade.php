@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link active" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a class="nav-link" href="{{ route('admin.reports.index') }}"><i class="bi bi-file-earmark-text me-2"></i>Reports</a>
<a class="nav-link" href="{{ route('admin.categories') }}"><i class="bi bi-tags me-2"></i>Categories</a>
<a class="nav-link" href="{{ route('admin.departments') }}"><i class="bi bi-building me-2"></i>Departments</a>
<a class="nav-link" href="{{ route('admin.users') }}"><i class="bi bi-people me-2"></i>Users</a>
<a class="nav-link" href="{{ route('admin.feedback') }}"><i class="bi bi-star me-2"></i>Feedback</a>
@endsection
@section('content')
<h1 class="h3 text-navy mb-4">Administrator Dashboard</h1>
<div class="row g-3 mb-4">
@foreach(['citizens'=>'Citizens','staff'=>'Staff','reports'=>'Reports','pending'=>'Pending','assigned'=>'Assigned','resolved'=>'Resolved','rejected'=>'Rejected'] as $key=>$label)<div class="col-sm-6 col-lg"><div class="card stat-card h-100"><div class="card-body"><small class="text-muted">{{ $label }}</small><h3 class="mb-0">{{ $stats[$key] }}</h3></div></div></div>@endforeach
</div>
<div class="card border-0 shadow-sm"><div class="card-header bg-white"><h2 class="h6 mb-0">Recent Reports</h2></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Reference</th><th>Title</th><th>Citizen</th><th>Status</th><th></th></tr></thead><tbody>@foreach($recentReports as $report)<tr><td>{{ $report->reference_number }}</td><td>{{ $report->title }}</td><td>{{ $report->citizen->name }}</td><td><span class="badge text-bg-secondary">{{ str_replace('_',' ', $report->status) }}</span></td><td><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.reports.show',$report) }}">Manage</a></td></tr>@endforeach</tbody></table></div></div>
<div class="row g-4 mt-1">
    <div class="col-lg-7"><div class="card border-0 shadow-sm"><div class="card-body"><h2 class="h6">Monthly Reports</h2><canvas id="monthlyChart"></canvas></div></div></div>
    <div class="col-lg-5"><div class="card border-0 shadow-sm"><div class="card-body"><h2 class="h6">Reports by Category</h2><canvas id="categoryChart"></canvas></div></div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('monthlyChart'), {type:'line',data:{labels:@json($monthlyReports->pluck('month')),datasets:[{label:'Reports',data:@json($monthlyReports->pluck('total')),borderColor:'#0c8f70',tension:.3}]},options:{responsive:true}});
new Chart(document.getElementById('categoryChart'), {type:'doughnut',data:{labels:@json($categories->pluck('name')),datasets:[{data:@json($categories->pluck('reports_count')),backgroundColor:['#0b1f33','#0c8f70','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#64748b']}]},options:{responsive:true}});
</script>
@endsection
