@extends('layouts.citizen')

@section('title', 'My reports')

@section('content')
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
        <button class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Search</button>
        <a href="{{ route('citizen.reports.index') }}" class="rounded-lg border border-line bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-sand-50">Clear</a>
    </form>

    <section class="mt-7 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
        @forelse ($reports as $report)
            <a href="{{ route('citizen.reports.show', $report) }}" class="flex flex-col gap-3 border-b border-line p-5 transition last:border-0 hover:bg-sand-50 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <span class="min-w-0"><span class="block truncate text-sm font-semibold text-slate-900">{{ $report->title }}</span><span class="mt-1 block text-xs text-slate-500">{{ $report->category->name }} · {{ $report->public_reference }} · {{ $report->submitted_at->format('M j, Y') }}</span></span>
                <span class="flex shrink-0 items-center gap-3"><span class="rounded-full bg-forest-50 px-2.5 py-1 text-xs font-semibold text-forest-900">{{ $report->status->label() }}</span><span aria-hidden="true" class="text-slate-400">→</span></span>
            </a>
        @empty
            <div class="p-8 text-center sm:p-12">
                <h2 class="text-base font-semibold text-slate-900">{{ filled($filters['q'] ?? null) || filled($filters['status'] ?? null) ? 'No reports match these filters' : 'No reports yet' }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ filled($filters['q'] ?? null) || filled($filters['status'] ?? null) ? 'Try another search term or status.' : 'Start a report when you see a civic service issue that needs attention.' }}</p>
                <a href="{{ route('citizen.reports.create') }}" class="mt-5 inline-flex rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Create a report</a>
            </div>
        @endforelse
    </section>
    <div class="mt-5">{{ $reports->links() }}</div>
@endsection
