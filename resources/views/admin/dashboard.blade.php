@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link active" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid"></i>Overview</a>
<a class="nav-link" href="{{ route('admin.reports.index') }}"><i class="bi bi-file-earmark-text"></i>Reports</a>
<a class="nav-link" href="{{ route('admin.categories') }}"><i class="bi bi-tags"></i>Categories</a>
<a class="nav-link" href="{{ route('admin.departments') }}"><i class="bi bi-building"></i>Departments</a>
<a class="nav-link" href="{{ route('admin.users') }}"><i class="bi bi-people"></i>People & staff</a>
<a class="nav-link" href="{{ route('admin.feedback') }}"><i class="bi bi-star"></i>Feedback</a>
@endsection
@section('content')
<div class="page-heading"><div><div class="section-eyebrow mb-2">CONTROL CENTER</div><h1>Good to see you, {{ auth()->user()->name }}</h1><p>Here is the latest overview of community reports and operations.</p></div><a href="{{ route('admin.reports.index') }}" class="btn btn-emerald"><i class="bi bi-arrow-right-circle me-2"></i>Review reports</a></div>
<div class="row g-3 mb-4">
@foreach(['citizens'=>'Citizens','staff'=>'Staff members','reports'=>'All reports','pending'=>'Awaiting review','assigned'=>'Assigned','resolved'=>'Resolved','rejected'=>'Rejected'] as $key=>$label)
<div class="col-6 col-md-4 col-xl"><div class="card stat-card h-100"><div class="card-body"><small>{{ $label }}</small><h3>{{ number_format($stats[$key]) }}</h3></div></div></div>
@endforeach
</div>
<div class="row g-4 mb-4">
<div class="col-xl-7"><div class="card h-100"><div class="card-header bg-white d-flex justify-content-between align-items-center"><div><h2 class="panel-title">Reporting activity</h2><div class="panel-subtitle">Monthly report volume</div></div><span class="stat-icon"><i class="bi bi-graph-up"></i></span></div><div class="card-body"><div style="height:270px"><canvas id="monthlyChart"></canvas></div></div></div></div>
<div class="col-xl-5"><div class="card h-100"><div class="card-header bg-white d-flex justify-content-between align-items-center"><div><h2 class="panel-title">Reports by category</h2><div class="panel-subtitle">Distribution across active categories</div></div><span class="stat-icon"><i class="bi bi-pie-chart"></i></span></div><div class="card-body"><div style="height:270px"><canvas id="categoryChart"></canvas></div></div></div></div>
</div>
<div class="card"><div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h2 class="panel-title">Recently submitted reports</h2><div class="panel-subtitle">The latest issues added to CivicTrack</div></div><a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">View all reports <i class="bi bi-arrow-right ms-1"></i></a></div>
<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Reference</th><th>Report</th><th>Submitted by</th><th>Status</th><th class="text-end">Action</th></tr></thead><tbody>
@forelse($recentReports as $report)<tr><td><span class="fw-semibold small">{{ $report->reference_number }}</span></td><td><div class="fw-semibold">{{ $report->title }}</div></td><td>{{ $report->citizen->name }}</td><td><span class="badge text-bg-secondary">{{ str_replace('_',' ', $report->status) }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.reports.show',$report) }}">Review <i class="bi bi-arrow-up-right ms-1"></i></a></td></tr>
@empty<tr><td colspan="5"><div class="empty-state"><i class="bi bi-inbox"></i><div class="fw-semibold">No reports yet</div><div class="small">New community reports will appear here.</div></div></td></tr>@endforelse
</tbody></table></div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const monthlyCanvas=document.getElementById('monthlyChart');
if(monthlyCanvas){new Chart(monthlyCanvas,{type:'line',data:{labels:@json($monthlyReports->pluck('month')),datasets:[{label:'Reports',data:@json($monthlyReports->pluck('total')),borderColor:'#176b52',backgroundColor:'rgba(23,107,82,.10)',fill:true,tension:.35,pointRadius:3,pointBackgroundColor:'#176b52'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#edf1ee'}}}}});}
const categoryCanvas=document.getElementById('categoryChart');
if(categoryCanvas){new Chart(categoryCanvas,{type:'doughnut',data:{labels:@json($categories->pluck('name')),datasets:[{data:@json($categories->pluck('reports_count')),backgroundColor:['#176b52','#8ab69c','#d5e6dc','#547b67','#b6c8bd','#345a49','#a3b6aa','#d9c7a4'],borderWidth:3,borderColor:'#fff'}]},options:{responsive:true,maintainAspectRatio:false,cutout:'68%',plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:7,padding:16}}}}});}
</script>
@endsection