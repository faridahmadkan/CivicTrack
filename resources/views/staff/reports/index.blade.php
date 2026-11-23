@extends($isAdministrator ? 'layouts.admin' : 'layouts.staff')

@section('title', 'Report queue')
@section('eyebrow', 'Department triage queue')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Service operations</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Report queue</h1>
            <p class="mt-2 text-sm text-slate-600">Review incoming issues routed to your active service units.</p>
        </div>
        <span class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $reports->total() }} reports</span>
    </div>
    <section class="mt-6 grid gap-3 sm:grid-cols-3" aria-label="Service workload summary">
        <article class="rounded-xl border border-line bg-white p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reports needing triage</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ number_format($triageCount) }}</p></article>
        <article class="rounded-xl border border-line bg-white p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Active field work</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ number_format($activeWorkCount) }}</p></article>
        <article class="rounded-xl border border-red-100 bg-white p-4"><p class="text-xs font-semibold uppercase tracking-wide text-red-700">Reports with overdue targets</p><p class="mt-2 text-2xl font-semibold text-red-900">{{ number_format($overdueCount) }}</p></article>
    </section>
    <form method="GET" action="{{ route('staff.reports.index') }}" class="mt-6 flex flex-wrap items-end gap-3 rounded-2xl border border-line bg-white p-4">
        <label class="text-xs font-semibold text-slate-600">Status
            <select name="status" class="mt-1.5 block rounded-lg border border-line bg-white px-3 py-2 text-sm font-normal text-slate-800">
                <option value="">Needs triage</option>
                @foreach (App\Enums\CivicReportStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>
        <button class="rounded-lg border border-line bg-white px-4 py-2 text-sm font-semibold text-forest-900 hover:bg-forest-50">Filter queue</button>
    </form>
    <section class="mt-5 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
        @forelse ($reports as $report)
            <a href="{{ route('staff.reports.show', $report) }}" class="grid gap-3 border-b border-line p-5 transition last:border-0 hover:bg-sand-50 md:grid-cols-[minmax(0,1fr)_11rem_8rem] md:items-center md:px-6">
                <span class="min-w-0"><span class="block truncate text-sm font-semibold text-slate-900">{{ $report->title }}</span><span class="mt-1 block text-xs text-slate-500">{{ $report->category->name }} · {{ $report->public_reference }}</span></span>
                <span class="text-xs text-slate-600">{{ $report->routedUnit?->name ?? 'Unassigned queue' }}</span>
                <span class="flex items-center justify-between gap-2"><span class="rounded-full bg-forest-50 px-2.5 py-1 text-xs font-semibold text-forest-900">{{ $report->status->label() }}</span><span aria-hidden="true" class="text-slate-400">→</span></span>
            </a>
        @empty
            <div class="p-8 text-center sm:p-12"><h2 class="text-base font-semibold text-slate-900">No reports in this queue</h2><p class="mt-2 text-sm text-slate-600">Reports routed to your service units will appear here for review.</p></div>
        @endforelse
    </section>
    <div class="mt-5">{{ $reports->links() }}</div>
@endsection
