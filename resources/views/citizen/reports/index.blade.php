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

    <section class="mt-7 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
        @forelse ($reports as $report)
            <a href="{{ route('citizen.reports.show', $report) }}" class="flex flex-col gap-3 border-b border-line p-5 transition last:border-0 hover:bg-sand-50 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <span class="min-w-0"><span class="block truncate text-sm font-semibold text-slate-900">{{ $report->title }}</span><span class="mt-1 block text-xs text-slate-500">{{ $report->category->name }} · {{ $report->public_reference }} · {{ $report->submitted_at->format('M j, Y') }}</span></span>
                <span class="flex shrink-0 items-center gap-3"><span class="rounded-full bg-forest-50 px-2.5 py-1 text-xs font-semibold text-forest-900">{{ $report->status->label() }}</span><span aria-hidden="true" class="text-slate-400">→</span></span>
            </a>
        @empty
            <div class="p-8 text-center sm:p-12">
                <h2 class="text-base font-semibold text-slate-900">No reports yet</h2>
                <p class="mt-2 text-sm text-slate-600">Start a report when you see a civic service issue that needs attention.</p>
                <a href="{{ route('citizen.reports.create') }}" class="mt-5 inline-flex rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Create a report</a>
            </div>
        @endforelse
    </section>
    <div class="mt-5">{{ $reports->links() }}</div>
@endsection
