@extends('layouts.dashboard')
@section('sidebar')
<a class="nav-link" href="{{ route('citizen.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a class="nav-link" href="{{ route('citizen.reports.index') }}"><i class="bi bi-file-earmark-text me-2"></i>My Reports</a>
<a class="nav-link active" href="{{ route('citizen.reports.create') }}"><i class="bi bi-plus-circle me-2"></i>Submit Report</a>
@endsection
@section('content')
<div class="page-heading"><div><div class="section-eyebrow mb-2">COMMUNITY REPORTING</div><h1>Tell us what needs attention</h1><p>Share a few details so the right team can review and respond.</p></div><span class="badge text-bg-light border text-secondary">Step 1 · Report details</span></div>
<div class="card border-0 shadow-sm"><div class="card-body p-4"><form method="POST" action="{{ route('citizen.reports.store') }}" enctype="multipart/form-data">@csrf
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Report title</label><input name="title" value="{{ old('title') }}" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select" required><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
<div class="col-md-6"><label class="form-label">Category</label><select name="category_id" class="form-select" required><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Location or address</label><input name="location" value="{{ old('location') }}" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Latitude (optional)</label><input type="number" step="any" name="latitude" value="{{ old('latitude') }}" class="form-control"></div>
<div class="col-md-6"><label class="form-label">Longitude (optional)</label><input type="number" step="any" name="longitude" value="{{ old('longitude') }}" class="form-control"></div>
<div class="col-12"><label class="form-label">Detailed description</label><textarea name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea></div>
<div class="col-12"><label class="form-label">Supporting photographs</label><input type="file" name="photos[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required data-preview="#reportPhotoPreview"><small class="text-muted">1–5 JPG, PNG or WebP images, 2 MB each.</small><div id="reportPhotoPreview" class="row g-2 mt-2"></div></div>
</div><div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-4"><p class="small text-secondary mb-0"><i class="bi bi-shield-check me-1"></i>Your report will be shared with the responsible civic team.</p><button class="btn btn-emerald px-4">Submit Report <i class="bi bi-arrow-right ms-1"></i></button></div></form></div></div>
@endsection
