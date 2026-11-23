@extends('layouts.citizen')

@section('title', 'New report')

@section('content')
    <div class="mx-auto max-w-4xl">
        <x-back-link href="{{ route('citizen.reports.index') }}">My reports</x-back-link>
        <p class="mt-7 text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Civic service request</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Report a community issue</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">Your report will be routed to the configured service unit, or placed in the shared triage queue if no route is configured.</p>
        <div class="mt-7 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7">
            @if ($categories->isEmpty())
                <x-empty-state title="The service catalog is unavailable" description="There are no active public categories right now. Please try again later." />
            @else
                @include('reports._form', ['action' => route('citizen.reports.store')])
            @endif
        </div>
    </div>
@endsection
