@extends('layouts.worker')

@section('title', $task->task_number)

@section('content')
    <x-back-link href="{{ route('worker.tasks.index') }}">My tasks</x-back-link>
    <div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="space-y-5">
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-mono text-xs font-semibold text-slate-500">{{ $task->task_number }} · {{ $task->workOrder->reference }}</p><h1 class="mt-2 text-2xl font-semibold tracking-tight text-forest-950">{{ $task->title }}</h1><p class="mt-2 text-sm text-slate-600">{{ $task->workOrder->report->public_reference }} · {{ $task->workOrder->report->category->name }}</p></div><x-status-badge :status="$task->status" class="px-3 py-1.5" /></div>
                <div class="mt-5 border-t border-line pt-5"><h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Work instructions</h2><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $task->instructions ?: $task->workOrder->instructions ?: 'No additional instructions were added.' }}</p>@if ($assignment->assignment_instructions)<p class="mt-3 rounded-lg bg-sand-50 p-3 text-sm leading-6 text-slate-700"><span class="font-semibold">Your assignment:</span> {{ $assignment->assignment_instructions }}</p>@endif</div>
                <div class="mt-5 border-t border-line pt-5"><h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Report location</h2><p class="mt-2 text-sm text-slate-700">{{ $task->workOrder->report->location?->address ?: 'Pinned service location' }}</p>@if ($task->workOrder->report->location?->latitude !== null)<p class="mt-1 font-mono text-xs text-slate-500">{{ $task->workOrder->report->location->latitude }}, {{ $task->workOrder->report->location->longitude }}</p>@endif</div>
            </section>

            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7"><h2 class="text-base font-semibold text-forest-950">Task record</h2><ol class="mt-4 space-y-3">@foreach ($task->updates as $update)<li class="rounded-xl bg-sand-50 p-4"><div class="flex flex-wrap justify-between gap-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ str($update->event_type)->replace('_', ' ')->headline() }}</p><time class="text-xs text-slate-500">{{ $update->occurred_at->format('M j, Y · g:i A') }}</time></div><p class="mt-1 text-xs text-slate-500">{{ $update->actor?->name ?? 'System' }}</p>@if ($update->body)<p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $update->body }}</p>@endif</li>@endforeach</ol></section>
        </div>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-forest-950">Your next step</h2>
                @if ($assignment->status === App\Enums\TaskAssignmentStatus::Assigned)
                    <p class="mt-2 text-xs leading-5 text-slate-600">Accept the assignment or return it to your service unit with a reason.</p>
                    <form method="POST" action="{{ route('worker.tasks.accept', $task) }}" class="mt-4">@csrf<button class="w-full rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Accept task</button></form>
                    <form method="POST" action="{{ route('worker.tasks.decline', $task) }}" class="mt-4 border-t border-line pt-4" onsubmit="return confirm('Return this assignment to the service unit?')">@csrf<label class="block text-xs font-semibold text-slate-600">Reason for declining<textarea name="body" rows="3" maxlength="2000" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal"></textarea></label><button class="button-danger mt-2">Return assignment</button></form>
                @elseif (in_array($assignment->status, [App\Enums\TaskAssignmentStatus::Accepted, App\Enums\TaskAssignmentStatus::NeedsRework], true))
                    <p class="mt-2 text-xs leading-5 text-slate-600">{{ $assignment->status === App\Enums\TaskAssignmentStatus::NeedsRework ? 'Staff requested a correction. Review their note and resume the task.' : 'Start when you are ready to begin the field work.' }}</p>
                    <form method="POST" action="{{ route('worker.tasks.start', $task) }}" class="mt-4">@csrf<button class="w-full rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">{{ $assignment->status === App\Enums\TaskAssignmentStatus::NeedsRework ? 'Resume correction' : 'Start work' }}</button></form>
                @elseif ($assignment->status === App\Enums\TaskAssignmentStatus::Submitted)
                    <p class="mt-2 text-sm leading-6 text-slate-600">Your evidence is with staff for verification. The task remains open until a decision is recorded.</p>
                @else
                    <p class="mt-2 text-sm text-slate-600">This assignment is complete.</p>
                @endif
            </section>

            @if (in_array($assignment->status, [App\Enums\TaskAssignmentStatus::InProgress, App\Enums\TaskAssignmentStatus::NeedsRework], true))
                <section class="rounded-2xl border border-line bg-white p-5 shadow-sm"><h2 class="text-sm font-semibold text-forest-950">Record progress</h2><form method="POST" action="{{ route('worker.tasks.updates.store', $task) }}" class="mt-3">@csrf<label class="block text-xs font-semibold text-slate-600">Update<textarea name="body" rows="3" maxlength="2000" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal" placeholder="What has been completed or what is blocking the work?"></textarea></label><button class="mt-2 rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-forest-900 hover:bg-forest-50">Save progress</button></form></section>
                <section class="rounded-2xl border border-line bg-white p-5 shadow-sm"><h2 class="text-sm font-semibold text-forest-950">Add work evidence</h2><p class="mt-1 text-xs leading-5 text-slate-500">Upload at least one file before sending this task for verification.</p><form method="POST" enctype="multipart/form-data" action="{{ route('worker.tasks.evidence.store', $task) }}" class="mt-3 space-y-3">@csrf<label class="block text-xs font-semibold text-slate-600">Evidence type<select name="evidence_type" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"><option value="progress">Progress</option><option value="before">Before work</option><option value="after">Completed work</option></select></label><label class="block text-xs font-semibold text-slate-600">Files<input type="file" name="evidence[]" multiple required accept="image/jpeg,image/png,image/webp,application/pdf" class="mt-1.5 block w-full rounded-lg border border-line px-2 py-2 text-xs font-normal"></label><label class="block text-xs font-semibold text-slate-600">Caption<textarea name="caption" rows="2" maxlength="500" class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal"></textarea></label><button class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-xs font-semibold text-forest-900 hover:bg-forest-50">Upload private evidence</button></form></section>
                @if ($evidence->isNotEmpty())<section class="rounded-2xl border border-line bg-white p-5 shadow-sm"><h2 class="text-sm font-semibold text-forest-950">Evidence on this assignment</h2><ul class="mt-3 space-y-2">@foreach ($evidence as $item)<li><a href="{{ route('worker.tasks.evidence.download', [$task, $item]) }}" class="break-all text-sm font-medium text-forest-800 underline underline-offset-2">{{ $item->original_name }}</a></li>@endforeach</ul></section>@endif
                <form method="POST" action="{{ route('worker.tasks.submit', $task) }}" class="rounded-2xl border border-forest-100 bg-forest-50 p-5">@csrf<h2 class="text-sm font-semibold text-forest-950">Submit for verification</h2><p class="mt-1 text-xs leading-5 text-forest-900/70">The service team will review your evidence and either approve the task or request a correction.</p><button class="mt-4 w-full rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Submit completed work</button></form>
            @endif
        </aside>
    </div>
@endsection
