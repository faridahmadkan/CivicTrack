@extends('layouts.citizen')

@section('title', 'Citizen overview')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Citizen workspace</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Good to see you, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Track the issues you reported, follow service decisions, and respond when a team needs more information.</p>
        </div>
        <a href="{{ route('citizen.reports.create') }}" class="button-primary">Report a service issue</a>
    </div>

    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Citizen report summary">
        <a href="{{ route('citizen.reports.index') }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reports submitted</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($totalReports) }}</p><span class="mt-2 inline-flex text-xs font-semibold text-forest-800">View your reports <span aria-hidden="true" class="ml-1 transition-transform group-hover:translate-x-0.5">→</span></span></a>
        <a href="{{ route('citizen.reports.index', ['scope' => 'open']) }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Open with service teams</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($openReports) }}</p><span class="mt-2 inline-flex text-xs font-semibold text-forest-800">Track open reports <span aria-hidden="true" class="ml-1 transition-transform group-hover:translate-x-0.5">→</span></span></a>
        <a href="{{ route('citizen.reports.index', ['status' => App\Enums\CivicReportStatus::Resolved->value]) }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Resolved</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($resolvedReports) }}</p><span class="mt-2 inline-flex text-xs font-semibold text-forest-800">Review resolved reports <span aria-hidden="true" class="ml-1 transition-transform group-hover:translate-x-0.5">→</span></span></a>
        <a href="{{ route('citizen.reports.index', ['scope' => 'feedback_due']) }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Feedback to provide</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($feedbackDue) }}</p><span class="mt-2 inline-flex text-xs font-semibold text-forest-800">Rate a service response <span aria-hidden="true" class="ml-1 transition-transform group-hover:translate-x-0.5">→</span></span></a>
    </section>

    <section class="mt-8 overflow-hidden rounded-2xl border border-line bg-white shadow-sm" aria-labelledby="recent-reports-heading">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4 sm:px-6">
            <div><h2 id="recent-reports-heading" class="text-base font-semibold text-forest-950">Recent reports</h2><p class="mt-1 text-xs text-slate-500">Your latest five service requests</p></div>
            <a href="{{ route('citizen.reports.index') }}" class="text-sm font-semibold text-forest-800 hover:text-forest-950">View all reports</a>
        </div>
        @forelse ($recentReports as $report)
            <article class="flex flex-col gap-4 border-b border-line p-5 transition last:border-0 hover:bg-sand-50 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <a href="{{ route('citizen.reports.show', $report) }}" class="group min-w-0 flex-1 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><span class="block truncate text-sm font-semibold text-slate-900 group-hover:text-forest-800">{{ $report->title }}</span><span class="mt-1 block text-xs text-slate-500">{{ $report->category->name }} · {{ $report->public_reference }} · {{ $report->submitted_at->format('M j, Y') }}</span><span class="mt-2 inline-flex text-xs font-semibold text-forest-800">Open report <span aria-hidden="true" class="ml-1">→</span></span></a>
                <x-report-location-preview :location="$report->location" />
                <span class="flex shrink-0 items-center"><x-status-badge :status="$report->status" /></span>
            </article>
        @empty
            <x-empty-state title="No reports yet" description="Start a report when you see a civic service issue that needs attention." :href="route('citizen.reports.create')" action="Create your first report" />
        @endforelse
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2" aria-label="Citizen services">
        <a href="{{ route('workforce.applications.index') }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40"><h2 class="text-sm font-semibold text-forest-950">Workforce applications</h2><p class="mt-2 text-sm leading-6 text-slate-600">You have {{ $workforceApplications }} staff or worker application record(s). Review status or apply when registration is available.</p><span class="mt-4 inline-flex text-sm font-semibold text-forest-800">Open applications →</span></a>
        <a href="{{ route('privacy.notice') }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40"><h2 class="text-sm font-semibold text-forest-950">Privacy and account data</h2><p class="mt-2 text-sm leading-6 text-slate-600">Review how report locations, contact details, attachments, and account information are handled.</p><span class="mt-4 inline-flex text-sm font-semibold text-forest-800">Read the privacy notice →</span></a>
    </section>
@endsection
