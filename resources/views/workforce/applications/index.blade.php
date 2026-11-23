@extends('layouts.citizen')

@section('title', 'Workforce applications')

@section('content')
    <a href="{{ route('citizen.home') }}" class="mb-6 inline-flex rounded-lg text-sm font-semibold text-forest-800 hover:text-forest-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">← Citizen dashboard</a>
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
        <div class="mb-3 flex items-center justify-between gap-4"><h2 id="application-history-heading" class="text-base font-semibold text-slate-900">Your application history</h2><span class="text-xs text-slate-500">{{ $applications->count() }} {{ \Illuminate\Support\Str::plural('application', $applications->count()) }}</span></div>
        @forelse ($applications as $application)
            <a href="{{ route('workforce.applications.show', $application) }}" class="mb-3 flex flex-col gap-4 rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:shadow-md sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ ucfirst($application->application_type->value) }} · {{ $application->application_number }}</p><h3 class="mt-1.5 text-base font-semibold text-slate-900">{{ $application->position_title }}</h3><p class="mt-1 text-sm text-slate-600">{{ $application->requestedUnit?->name ?? 'Organization unit unavailable' }} · Submitted {{ $application->submitted_at->format('M j, Y') }}</p></div>
                @php($statusColors = [App\Enums\WorkforceApplicationStatus::Approved->value => 'bg-forest-50 text-forest-800', App\Enums\WorkforceApplicationStatus::Rejected->value => 'bg-red-50 text-red-800', App\Enums\WorkforceApplicationStatus::InformationRequested->value => 'bg-amber-50 text-amber-900'])
                <span class="w-fit rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusColors[$application->status->value] ?? 'bg-sand-100 text-slate-700' }}">{{ $application->status->label() }}</span>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-14 text-center"><span class="mx-auto flex size-11 items-center justify-center rounded-xl bg-forest-50 text-forest-800"><svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M8 5h8l4 4v11H4V5h4Zm7 0v5h5M8 14h8m-8 3h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span><p class="mt-4 text-sm font-semibold text-slate-900">No applications yet</p><p class="mt-1 text-sm text-slate-500">Submitted applications and their review history will appear here.</p></div>
        @endforelse
    </section>
@endsection
