@extends('layouts.worker')

@section('title', 'My field tasks')

@section('content')
    @php
        $awaitingResponse = $assignments->where('status', App\Enums\TaskAssignmentStatus::Assigned)->count();
        $inProgress = $assignments->whereIn('status', [App\Enums\TaskAssignmentStatus::Accepted, App\Enums\TaskAssignmentStatus::InProgress, App\Enums\TaskAssignmentStatus::NeedsRework])->count();
        $submitted = $assignments->where('status', App\Enums\TaskAssignmentStatus::Submitted)->count();
    @endphp
    <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Field workforce</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Your assigned tasks</h1><p class="mt-2 text-sm text-slate-600">Accept your assignments, record progress, and submit evidence for verification.</p></div><span class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $assignments->count() }} active</span></div>
    <section class="mt-6 grid gap-3 sm:grid-cols-3" aria-label="Task summary"><article class="rounded-xl border border-line bg-white p-4"><p class="text-xs font-semibold text-slate-500">Awaiting your response</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ $awaitingResponse }}</p></article><article class="rounded-xl border border-line bg-white p-4"><p class="text-xs font-semibold text-slate-500">Accepted or in progress</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ $inProgress }}</p></article><article class="rounded-xl border border-line bg-white p-4"><p class="text-xs font-semibold text-slate-500">Awaiting staff verification</p><p class="mt-2 text-2xl font-semibold text-forest-950">{{ $submitted }}</p></article></section>
    <section class="mt-6 space-y-3" aria-label="Assigned field tasks">
        @forelse ($assignments as $assignment)
            @php($task = $assignment->task)
            <a href="{{ route('worker.tasks.show', $task) }}" class="block rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/30 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-mono text-[0.68rem] font-semibold text-slate-500">{{ $task->task_number }} · {{ $task->workOrder->reference }}</p><h2 class="mt-1 text-base font-semibold text-forest-950">{{ $task->title }}</h2><p class="mt-1 text-xs text-slate-500">{{ $task->workOrder->report->public_reference }} · {{ $task->workOrder->report->category->name }}</p></div><span class="rounded-full bg-forest-50 px-2.5 py-1 text-xs font-semibold text-forest-900">{{ str($assignment->status->value)->replace('_', ' ')->headline() }}</span></div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-3 text-xs text-slate-500"><span>Assigned {{ $assignment->assigned_at->format('M j, Y') }}</span><span class="font-semibold text-forest-800">Open task →</span></div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-line bg-white p-8 text-center sm:p-12"><h2 class="text-base font-semibold text-slate-900">No active tasks</h2><p class="mt-2 text-sm leading-6 text-slate-600">New assignments from your service unit will appear here.</p></div>
        @endforelse
    </section>
@endsection
