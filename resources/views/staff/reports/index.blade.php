@extends($isAdministrator ? 'layouts.admin' : 'layouts.staff')

@section('title', 'Report queue')
@section('eyebrow', 'Department triage queue')

@section('content')
    @php($hasActiveFilters = filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || filled($filters['preset'] ?? null))
    <x-back-link href="{{ route('home') }}" class="mb-6">Account overview</x-back-link>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Service operations</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Report queue</h1>
            <p class="mt-2 text-sm text-slate-600">Review incoming issues routed to your active service units.</p>
        </div>
        <span class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $reports->total() }} reports</span>
    </div>

    <section class="mt-6 grid gap-3 sm:grid-cols-3" aria-label="Service workload summary">
        <a href="{{ route('staff.reports.index') }}" class="rounded-xl border border-line bg-white p-4 transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reports needing triage</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ number_format($triageCount) }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">Open triage queue</span></a>
        <a href="{{ route('staff.reports.index', ['preset' => 'active_work']) }}" class="rounded-xl border border-line bg-white p-4 transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Active field work</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ number_format($activeWorkCount) }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">View active reports</span></a>
        <a href="{{ route('staff.reports.index', ['preset' => 'overdue']) }}" class="rounded-xl border border-red-100 bg-white p-4 transition hover:border-red-300 hover:bg-red-50/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-800"><p class="text-xs font-semibold uppercase tracking-wide text-red-800">Reports with overdue targets</p><p class="mt-2 text-2xl font-semibold text-red-950">{{ number_format($overdueCount) }}</p><span class="mt-1 block text-xs font-semibold text-red-800">Review overdue reports</span></a>
    </section>

    <nav class="mt-5 flex flex-wrap gap-2" aria-label="Saved report queue filters">
        <a href="{{ route('staff.reports.index', ['preset' => 'urgent']) }}" class="button-secondary">Urgent <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-950">{{ number_format($urgentCount) }}</span></a>
        <a href="{{ route('staff.reports.index', ['preset' => 'overdue']) }}" class="button-secondary">Overdue <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-950">{{ number_format($overdueCount) }}</span></a>
        <a href="{{ route('staff.reports.index', ['preset' => 'needs_information']) }}" class="button-secondary">Needs information <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-950">{{ number_format($needsInformationCount) }}</span></a>
        <a href="{{ route('staff.reports.index', ['preset' => 'reopen_requests']) }}" class="button-secondary">Reopen reviews <span class="rounded-full bg-sand-100 px-2 py-0.5 text-xs text-slate-800">{{ number_format($reopenReviewCount) }}</span></a>
    </nav>

    <form method="GET" action="{{ route('staff.reports.index') }}" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-[minmax(14rem,1fr)_14rem_auto_auto] sm:items-end">
        @if (filled($filters['preset'] ?? null))<input type="hidden" name="preset" value="{{ $filters['preset'] }}">@endif
        <label class="text-xs font-semibold text-slate-600">Search reports
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800" placeholder="Title, reference, or category">
        </label>
        <label class="text-xs font-semibold text-slate-600">Status
            <select name="status" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800">
                <option value="">{{ filled($filters['preset'] ?? null) ? 'All preset statuses' : 'Needs triage' }}</option>
                @foreach (App\Enums\CivicReportStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>
        <button class="button-primary">Search queue</button>
        <a href="{{ route('staff.reports.index') }}" class="button-secondary">Clear</a>
    </form>

    <section class="mt-5 overflow-hidden rounded-2xl border border-line bg-white shadow-sm" aria-label="Reports in queue">
        @forelse ($reports as $report)
            <article class="flex flex-col gap-4 border-b border-line p-5 transition last:border-0 hover:bg-sand-50 md:flex-row md:items-center md:justify-between md:px-6">
                <a href="{{ route('staff.reports.show', $report) }}" class="group min-w-0 flex-1 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><span class="block truncate text-sm font-semibold text-slate-900 group-hover:text-forest-800">{{ $report->title }}</span><span class="mt-1 block text-xs text-slate-500">{{ $report->category->name }} · {{ $report->public_reference }}</span><span class="mt-1 block text-xs text-slate-600">{{ $report->routedUnit?->name ?? 'Unassigned queue' }}</span><span class="mt-2 inline-flex text-xs font-semibold text-forest-800">Open report <span aria-hidden="true" class="ml-1">→</span></span></a>
                <x-report-location-preview :location="$report->location" />
                <span class="flex items-center justify-between gap-2 md:justify-end"><x-status-badge :status="$report->status" /></span>
            </article>
        @empty
            @if ($hasActiveFilters)
                <x-empty-state title="No reports match these filters" description="Try a different status or search term, or clear the filters to return to your queue." :href="route('staff.reports.index')" action="Clear filters" />
            @else
                <x-empty-state title="No reports need triage" description="Reports routed to your active service units will appear here when they need review." />
            @endif
        @endforelse
    </section>
    <div class="mt-5">{{ $reports->links() }}</div>
@endsection
