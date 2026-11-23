@extends('layouts.citizen')

@section('title', 'Citizen overview')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Citizen workspace</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Good to see you, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Track the issues you reported, follow service decisions, and respond when a team needs more information.</p>
        </div>
        <a href="{{ route('citizen.reports.create') }}" class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Report a service issue</a>
    </div>

    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Citizen report summary">
        <article class="rounded-2xl border border-line bg-white p-5 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reports submitted</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($totalReports) }}</p></article>
        <article class="rounded-2xl border border-line bg-white p-5 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Open with service teams</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($openReports) }}</p></article>
        <article class="rounded-2xl border border-line bg-white p-5 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Resolved</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($resolvedReports) }}</p></article>
        <article class="rounded-2xl border border-line bg-white p-5 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Feedback to provide</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($feedbackDue) }}</p></article>
    </section>

    <section class="mt-8 overflow-hidden rounded-2xl border border-line bg-white shadow-sm" aria-labelledby="recent-reports-heading">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4 sm:px-6">
            <div><h2 id="recent-reports-heading" class="text-base font-semibold text-forest-950">Recent reports</h2><p class="mt-1 text-xs text-slate-500">Your latest five service requests</p></div>
            <a href="{{ route('citizen.reports.index') }}" class="text-sm font-semibold text-forest-800 hover:text-forest-950">View all reports</a>
        </div>
        @forelse ($recentReports as $report)
            <a href="{{ route('citizen.reports.show', $report) }}" class="flex flex-col gap-3 border-b border-line p-5 transition last:border-0 hover:bg-sand-50 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <span class="min-w-0"><span class="block truncate text-sm font-semibold text-slate-900">{{ $report->title }}</span><span class="mt-1 block text-xs text-slate-500">{{ $report->category->name }} · {{ $report->public_reference }} · {{ $report->submitted_at->format('M j, Y') }}</span></span>
                <span class="flex shrink-0 items-center gap-3"><span class="rounded-full bg-forest-50 px-2.5 py-1 text-xs font-semibold text-forest-900">{{ $report->status->label() }}</span><span aria-hidden="true" class="text-slate-400">→</span></span>
            </a>
        @empty
            <div class="p-8 text-center sm:p-12"><h3 class="text-base font-semibold text-slate-900">No reports yet</h3><p class="mt-2 text-sm leading-6 text-slate-600">A clear location and a few details help the right service team respond.</p><a href="{{ route('citizen.reports.create') }}" class="mt-5 inline-flex rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Create your first report</a></div>
        @endforelse
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2" aria-label="Citizen services">
        <a href="{{ route('workforce.applications.index') }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40"><h2 class="text-sm font-semibold text-forest-950">Workforce applications</h2><p class="mt-2 text-sm leading-6 text-slate-600">You have {{ $workforceApplications }} staff or worker application record(s). Review status or apply when registration is available.</p><span class="mt-4 inline-flex text-sm font-semibold text-forest-800">Open applications →</span></a>
        <a href="{{ route('privacy.notice') }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40"><h2 class="text-sm font-semibold text-forest-950">Privacy and account data</h2><p class="mt-2 text-sm leading-6 text-slate-600">Review how report locations, contact details, attachments, and account information are handled.</p><span class="mt-4 inline-flex text-sm font-semibold text-forest-800">Read the privacy notice →</span></a>
    </section>
@endsection
