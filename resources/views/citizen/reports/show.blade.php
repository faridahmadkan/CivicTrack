@extends('layouts.citizen')

@section('title', 'Report '.$report->public_reference)

@section('content')
    <div class="mx-auto max-w-6xl">
        <a href="{{ route('citizen.reports.index') }}" class="mb-6 inline-flex rounded-lg text-sm font-semibold text-forest-800 hover:text-forest-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">← My reports</a>
        @include('reports._details', ['guestAccess' => false])
    </div>
@endsection
