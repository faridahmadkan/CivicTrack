@extends('layouts.citizen')

@section('title', 'Workforce applications')

@section('content')
    <x-back-link href="{{ route('citizen.home') }}" class="mb-6">Citizen dashboard</x-back-link>
    <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Career with CivicTrack services</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Workforce applications</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Staff coordinators and field workers have separate approval processes and access. Applying does not provide operational permissions.</p></div>
        @if ($canApply)
            <div class="flex flex-wrap gap-2">
                @if ($staffApplicationsOpen)<a href="{{ route('workforce.applications.create', 'staff') }}" class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-800">Apply for staff</a>@endif
                @if ($workerApplicationsOpen)<a href="{{ route('workforce.applications.create', 'worker') }}" class="rounded-lg border border-forest-900/15 bg-white px-4 py-2.5 text-sm font-semibold text-forest-900 transition hover:bg-forest-50">Apply as field worker</a>@endif
            </div>
        @endif
    </div>

    @if (! $canApply)
        <div class="mt-7 rounded-2xl border border-forest-100 bg-forest-50 p-5"><p class="text-sm font-semibold text-forest-950">Your account already has workforce access.</p><p class="mt-1 text-sm leading-6 text-forest-900/80">Staff and worker responsibilities are kept separate. Contact an administrator if you believe your role needs to change.</p></div>
    @elseif (! $staffApplicationsOpen && ! $workerApplicationsOpen)
        <div class="mt-7 rounded-2xl border border-line bg-white p-5"><p class="text-sm font-semibold text-slate-900">Applications are currently closed</p><p class="mt-1 text-sm leading-6 text-slate-600">The operating organization has not opened staff or field worker applications at this time.</p></div>
    @endif

    <section class="mt-8" aria-labelledby="application-history-heading">
        <div class="mb-3 flex items-center justify-between gap-4"><h2 id="application-history-heading" class="text-base font-semibold text-slate-900">Your application history</h2><span class="text-xs text-slate-500">{{ $applications->total() }} {{ \Illuminate\Support\Str::plural('application', $applications->total()) }}</span></div>
        <form method="GET" action="{{ route('workforce.applications.index') }}" class="mb-4 grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-2 xl:grid-cols-[minmax(14rem,1fr)_12rem_14rem_auto_auto] xl:items-end">
            <label class="text-xs font-semibold text-slate-600">Search applications
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800" placeholder="Position, reference, or unit">
            </label>
            <label class="text-xs font-semibold text-slate-600">Application type
                <select name="type" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All types</option>@foreach (App\Enums\WorkforceApplicationType::cases() as $type)<option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ ucfirst($type->value) }}</option>@endforeach</select>
            </label>
            <label class="text-xs font-semibold text-slate-600">Status
                <select name="status" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All statuses</option>@foreach (App\Enums\WorkforceApplicationStatus::cases() as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
            </label>
            <button class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Search</button>
            <a href="{{ route('workforce.applications.index') }}" class="rounded-lg border border-line bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-sand-50">Clear</a>
        </form>
        @forelse ($applications as $application)
            <a href="{{ route('workforce.applications.show', $application) }}" class="mb-3 flex flex-col gap-4 rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:shadow-md sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ ucfirst($application->application_type->value) }} · {{ $application->application_number }}</p><h3 class="mt-1.5 text-base font-semibold text-slate-900">{{ $application->position_title }}</h3><p class="mt-1 text-sm text-slate-600">{{ $application->requestedUnit?->name ?? 'Organization unit unavailable' }} · Submitted {{ $application->submitted_at->format('M j, Y') }}</p></div>
                <x-status-badge :status="$application->status" />
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-14 text-center"><span class="mx-auto flex size-11 items-center justify-center rounded-xl bg-forest-50 text-forest-800"><svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M8 5h8l4 4v11H4V5h4Zm7 0v5h5M8 14h8m-8 3h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span><p class="mt-4 text-sm font-semibold text-slate-900">{{ filled($filters['q'] ?? null) || filled($filters['type'] ?? null) || filled($filters['status'] ?? null) ? 'No applications match these filters' : 'No applications yet' }}</p><p class="mt-1 text-sm text-slate-500">{{ filled($filters['q'] ?? null) || filled($filters['type'] ?? null) || filled($filters['status'] ?? null) ? 'Try another search term, type, or status.' : 'Submitted applications and their review history will appear here.' }}</p></div>
        @endforelse
        @if ($applications->hasPages())
            <div class="mt-4">{{ $applications->links() }}</div>
        @endif
    </section>
@endsection
