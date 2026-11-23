@extends('layouts.worker')

@section('title', 'My field tasks')

@section('content')
    @php
        $awaitingResponse = $assignmentCounts->get(App\Enums\TaskAssignmentStatus::Assigned->value, 0);
        $inProgress = $assignmentCounts->get(App\Enums\TaskAssignmentStatus::Accepted->value, 0) + $assignmentCounts->get(App\Enums\TaskAssignmentStatus::InProgress->value, 0) + $assignmentCounts->get(App\Enums\TaskAssignmentStatus::NeedsRework->value, 0);
        $submitted = $assignmentCounts->get(App\Enums\TaskAssignmentStatus::Submitted->value, 0);
        $activeAssignmentCount = $assignmentCounts->sum();
        $hasActiveFilters = filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || filled($filters['bucket'] ?? null);
    @endphp
    <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Field workforce</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Your assigned tasks</h1><p class="mt-2 text-sm text-slate-600">Accept your assignments, record progress, and submit evidence for verification.</p></div><a href="{{ route('worker.tasks.index') }}" class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-forest-200 hover:bg-forest-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">{{ $activeAssignmentCount }} active tasks</a></div>

    <section class="mt-6 grid gap-3 sm:grid-cols-3" aria-label="Task summary">
        <a href="{{ route('worker.tasks.index', ['bucket' => 'awaiting_response']) }}" class="rounded-xl border border-line bg-white p-4 transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold text-slate-500">Awaiting your response</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ $awaitingResponse }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">Review assignments</span></a>
        <a href="{{ route('worker.tasks.index', ['bucket' => 'in_progress']) }}" class="rounded-xl border border-line bg-white p-4 transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold text-slate-500">Accepted or in progress</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ $inProgress }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">Continue field work</span></a>
        <a href="{{ route('worker.tasks.index', ['bucket' => 'awaiting_verification']) }}" class="rounded-xl border border-line bg-white p-4 transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold text-slate-500">Awaiting staff verification</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ $submitted }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">View submitted work</span></a>
    </section>

    <form method="GET" action="{{ route('worker.tasks.index') }}" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-[minmax(14rem,1fr)_14rem_auto_auto] sm:items-end">
        @if (filled($filters['bucket'] ?? null))<input type="hidden" name="bucket" value="{{ $filters['bucket'] }}">@endif
        <label class="text-xs font-semibold text-slate-600">Search assigned tasks
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800" placeholder="Task, report, reference, or category">
        </label>
        <label class="text-xs font-semibold text-slate-600">Assignment status
            <select name="status" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All active statuses</option>@foreach ([App\Enums\TaskAssignmentStatus::Assigned, App\Enums\TaskAssignmentStatus::Accepted, App\Enums\TaskAssignmentStatus::InProgress, App\Enums\TaskAssignmentStatus::Submitted, App\Enums\TaskAssignmentStatus::NeedsRework] as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ str($status->value)->replace('_', ' ')->headline() }}</option>@endforeach</select>
        </label>
        <button class="button-primary">Search tasks</button>
        <a href="{{ route('worker.tasks.index') }}" class="button-secondary">Clear</a>
    </form>

    <section class="mt-6 space-y-3" aria-label="Assigned field tasks">
        @forelse ($assignments as $assignment)
            @php($task = $assignment->task)
            <article class="rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/30 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3"><a href="{{ route('worker.tasks.show', $task) }}" class="group min-w-0 flex-1 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="font-mono text-[0.68rem] font-semibold text-slate-500">{{ $task->task_number }} / {{ $task->workOrder->reference }}</p><h2 class="mt-1 text-base font-semibold text-forest-950 group-hover:text-forest-800">{{ $task->title }}</h2><p class="mt-1 text-xs text-slate-500">{{ $task->workOrder->report->public_reference }} / {{ $task->workOrder->report->category->name }}</p><span class="mt-2 inline-flex text-xs font-semibold text-forest-800">Open task</span></a><x-status-badge :status="$assignment->status" /></div>
                <div class="mt-4 grid gap-4 border-t border-line pt-3 sm:grid-cols-[minmax(0,1fr)_10rem] sm:items-end">
                    <x-report-location-preview :location="$task->workOrder->report->location" />
                    <span class="text-xs text-slate-500">Assigned {{ $assignment->assigned_at->format('M j, Y') }}</span>
                </div>
            </article>
        @empty
            @if ($hasActiveFilters)
                <x-empty-state title="No tasks match these filters" description="Try another search term or status, or clear the filters to see your active assignments." :href="route('worker.tasks.index')" action="Clear filters" />
            @else
                <x-empty-state title="No active tasks" description="New assignments from your service unit will appear here." />
            @endif
        @endforelse
    </section>
    <div class="mt-5">{{ $assignments->links() }}</div>
@endsection
