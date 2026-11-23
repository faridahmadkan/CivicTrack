@extends('layouts.citizen')

@section('title', 'My reports')

@section('content')
    @php($hasActiveFilters = filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || filled($filters['scope'] ?? null))
    <x-back-link href="{{ route('citizen.home') }}" class="mb-6">Citizen dashboard</x-back-link>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Citizen workspace</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">My reports</h1>
            <p class="mt-2 text-sm text-slate-600">Follow service updates and add information to reports you submitted.</p>
        </div>
        <a href="{{ route('citizen.reports.create') }}" class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">New report</a>
    </div>

    <form method="GET" action="{{ route('citizen.reports.index') }}" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-[minmax(14rem,1fr)_14rem_auto_auto] sm:items-end">
        @if (filled($filters['scope'] ?? null))<input type="hidden" name="scope" value="{{ $filters['scope'] }}">@endif
        <label class="text-xs font-semibold text-slate-600">Search your reports
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800" placeholder="Title, reference, or category">
        </label>
        <label class="text-xs font-semibold text-slate-600">Status
            <select name="status" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800">
                <option value="">All statuses</option>
                @foreach (App\Enums\CivicReportStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>
        <button class="button-primary">Search</button>
        <a href="{{ route('citizen.reports.index') }}" class="button-secondary">Clear</a>
    </form>

    <section class="mt-7 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
        @forelse ($reports as $report)
            <article class="flex flex-col gap-4 border-b border-line p-5 transition last:border-0 hover:bg-sand-50 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <a href="{{ route('citizen.reports.show', $report) }}" class="group min-w-0 flex-1 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><span class="block truncate text-sm font-semibold text-slate-900 group-hover:text-forest-800">{{ $report->title }}</span><span class="mt-1 block text-xs text-slate-500">{{ $report->category->name }} · {{ $report->public_reference }} · {{ $report->submitted_at->format('M j, Y') }}</span><span class="mt-2 inline-flex text-xs font-semibold text-forest-800">Open report <span aria-hidden="true" class="ml-1">→</span></span></a>
                <x-report-location-preview :location="$report->location" />
                <span class="flex shrink-0 items-center"><x-status-badge :status="$report->status" /></span>
            </article>
        @empty
            @if ($hasActiveFilters)
                <x-empty-state title="No reports match these filters" description="Try another search term or status, or clear the filters to see all of your reports." :href="route('citizen.reports.index')" action="Clear filters" />
            @else
                <x-empty-state title="No reports yet" description="Start a report when you see a civic service issue that needs attention." :href="route('citizen.reports.create')" action="Create a report" />
            @endif
        @endforelse
    </section>
    <div class="mt-5">{{ $reports->links() }}</div>
@endsection
