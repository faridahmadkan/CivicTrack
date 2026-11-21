@extends('layouts.public')
@section('content')
<main class="container py-5">
    <h1 class="fw-bold text-navy mb-4">How It Works</h1>
    <div class="row g-4">
        @foreach([
            ['1', 'Report', 'Describe the issue, add a location, and upload evidence.'],
            ['2', 'Review', 'Administrators verify and approve valid reports.'],
            ['3', 'Assign', 'Approved reports are assigned to qualified staff.'],
            ['4', 'Resolve', 'Staff investigate, update progress, and submit evidence.'],
            ['5', 'Feedback', 'Citizens rate the resolution and share feedback.'],
        ] as $step)
        <div class="col-md">
            <div class="card border-0 shadow-sm h-100"><div class="card-body"><span class="badge bg-emerald mb-3">{{ $step[0] }}</span><h5>{{ $step[1] }}</h5><p class="text-muted mb-0">{{ $step[2] }}</p></div></div>
        </div>
        @endforeach
    </div>
</main>
@endsection
