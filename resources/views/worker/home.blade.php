@extends('layouts.worker')

@section('title', 'My field tasks')

@section('content')
    @php
        $awaitingResponse = $assignmentCounts->get(App\Enums\TaskAssignmentStatus::Assigned->value, 0);
        $inProgress = $assignmentCounts->get(App\Enums\TaskAssignmentStatus::Accepted->value, 0) + $assignmentCounts->get(App\Enums\TaskAssignmentStatus::InProgress->value, 0) + $assignmentCounts->get(App\Enums\TaskAssignmentStatus::NeedsRework->value, 0);
        $submitted = $assignmentCounts->get(App\Enums\TaskAssignmentStatus::Submitted->value, 0);
        $activeAssignmentCount = $assignmentCounts->sum();
    @endphp
    <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Field workforce</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Your assigned tasks</h1><p class="mt-2 text-sm text-slate-600">Accept your assignments, record progress, and submit evidence for verification.</p></div><span class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $activeAssignmentCount }} active</span></div>
    <section class="mt-6 grid gap-3 sm:grid-cols-3" aria-label="Task summary"><article class="rounded-xl border border-line bg-white p-4"><p class="text-xs font-semibold text-slate-500">Awaiting your response</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ $awaitingResponse }}</p></article><article class="rounded-xl border border-line bg-white p-4"><p class="text-xs font-semibold text-slate-500">Accepted or in progress</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ $inProgress }}</p></article><article class="rounded-xl border border-line bg-white p-4"><p class="text-xs font-semibold text-slate-500">Awaiting staff verification</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ $submitted }}</p></article></section>
    <form method="GET" action="{{ route('worker.tasks.index') }}" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-[minmax(14rem,1fr)_14rem_auto_auto] sm:items-end">
        <label class="text-xs font-semibold text-slate-600">Search assigned tasks
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800" placeholder="Task, report, reference, or category">
        </label>
        <label class="text-xs font-semibold text-slate-600">Assignment status
            <select name="status" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All active statuses</option>@foreach ([App\Enums\TaskAssignmentStatus::Assigned, App\Enums\TaskAssignmentStatus::Accepted, App\Enums\TaskAssignmentStatus::InProgress, App\Enums\TaskAssignmentStatus::Submitted, App\Enums\TaskAssignmentStatus::NeedsRework] as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ str($status->value)->replace('_', ' ')->headline() }}</option>@endforeach</select>
        </label>
        <button class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Search tasks</button>
        <a href="{{ route('worker.tasks.index') }}" class="rounded-lg border border-line bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-sand-50">Clear</a>
    </form>
    <section class="mt-6 space-y-3" aria-label="Assigned field tasks">
        @forelse ($assignments as $assignment)
            @php($task = $assignment->task)
            <a href="{{ route('worker.tasks.show', $task) }}" class="block rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/30 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-mono text-[0.68rem] font-semibold text-slate-500">{{ $task->task_number }} · {{ $task->workOrder->reference }}</p><h2 class="mt-1 text-base font-semibold text-forest-950">{{ $task->title }}</h2><p class="mt-1 text-xs text-slate-500">{{ $task->workOrder->report->public_reference }} · {{ $task->workOrder->report->category->name }}</p></div><span class="rounded-full bg-forest-50 px-2.5 py-1 text-xs font-semibold text-forest-900">{{ str($assignment->status->value)->replace('_', ' ')->headline() }}</span></div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-3 text-xs text-slate-500"><span>Assigned {{ $assignment->assigned_at->format('M j, Y') }}</span><span class="font-semibold text-forest-800">Open task →</span></div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-line bg-white p-8 text-center sm:p-12"><h2 class="text-base font-semibold text-slate-900">{{ filled($filters['q'] ?? null) || filled($filters['status'] ?? null) ? 'No tasks match these filters' : 'No active tasks' }}</h2><p class="mt-2 text-sm leading-6 text-slate-600">{{ filled($filters['q'] ?? null) || filled($filters['status'] ?? null) ? 'Try another search term or status.' : 'New assignments from your service unit will appear here.' }}</p></div>
        @endforelse
    </section>
    <div class="mt-5">{{ $assignments->links() }}</div>
@endsection
