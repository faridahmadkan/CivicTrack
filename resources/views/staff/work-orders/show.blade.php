@extends($isAdministrator ? 'layouts.admin' : 'layouts.staff')

@section('title', $workOrder->reference)
@section('eyebrow', 'Work order and task coordination')

@section('content')
    <a href="{{ route('staff.reports.show', $workOrder->report) }}" class="text-sm font-semibold text-forest-800 hover:text-forest-950">← Report {{ $workOrder->report->public_reference }}</a>
    <div class="mt-5 flex flex-wrap items-start justify-between gap-4">
        <div><p class="font-mono text-xs font-semibold text-slate-500">{{ $workOrder->reference }} · {{ $workOrder->organizationUnit->name }}</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">{{ $workOrder->title }}</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $workOrder->instructions ?: 'No work order instructions were added.' }}</p></div>
        <span class="rounded-full bg-forest-50 px-3 py-1.5 text-xs font-semibold text-forest-900">{{ str($workOrder->status->value)->headline() }}</span>
    </div>

    <section class="mt-7 space-y-4" aria-label="Work tasks">
        @foreach ($workOrder->tasks as $task)
            <article class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line pb-4">
                    <div><p class="font-mono text-[0.68rem] font-semibold text-slate-500">{{ $task->task_number }} · Step {{ $task->sequence }}</p><h2 class="mt-1 text-lg font-semibold text-forest-950">{{ $task->title }}</h2><p class="mt-1 text-sm leading-6 text-slate-600">{{ $task->instructions ?: 'No additional instructions.' }}</p></div>
                    <span class="rounded-full bg-sand-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $task->status->label() }} · {{ $task->priority->label() }}</span>
                </div>
                @if ($task->dependencies->isNotEmpty())<p class="mt-3 text-xs text-slate-500">Prerequisites: {{ $task->dependencies->pluck('title')->join(', ') }}</p>@endif
                <div class="mt-4 grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Assigned workers</p>
                        @if ($task->activeAssignments->isNotEmpty())
                            @foreach ($task->activeAssignments as $assignment)
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $assignment->worker->name }} <span class="font-normal text-slate-500">· {{ str($assignment->status->value)->replace('_', ' ')->headline() }}</span></p>
                            @if ($assignment->assignment_instructions)<p class="mt-1 text-xs leading-5 text-slate-600">{{ $assignment->assignment_instructions }}</p>@endif
                                                    @endforeach
                        @else
                            <p class="mt-1 text-sm text-slate-500">Waiting for an assignment.</p>
                        @endif
                        @if ($task->evidence->isNotEmpty())
                            <p class="mt-4 text-xs font-bold uppercase tracking-wide text-slate-500">Submitted evidence</p>
                            <ul class="mt-2 space-y-1">@foreach ($task->evidence as $evidence)<li><a href="{{ route('staff.tasks.evidence.download', [$task, $evidence]) }}" class="text-sm font-medium text-forest-800 underline underline-offset-2">{{ $evidence->original_name }}</a>@if ($evidence->caption)<span class="ml-2 text-xs text-slate-500">{{ $evidence->caption }}</span>@endif</li>@endforeach</ul>
                        @endif
                        @if ($task->updates->isNotEmpty())<div class="mt-4 space-y-2">@foreach ($task->updates as $update)<div class="rounded-lg bg-sand-50 px-3 py-2"><p class="text-[0.68rem] font-semibold uppercase tracking-wide text-slate-500">{{ str($update->event_type)->replace('_', ' ')->headline() }} · {{ $update->actor?->name ?? 'System' }} · {{ $update->occurred_at->format('M j, g:i A') }}</p>@if ($update->body)<p class="mt-1 whitespace-pre-line text-xs leading-5 text-slate-600">{{ $update->body }}</p>@endif</div>@endforeach</div>@endif
                    </div>
                    <div class="space-y-4">
                        @if ($eligibleWorkers->reject(fn ($worker) => $task->activeAssignments->contains('worker_id', $worker->id))->isNotEmpty() && $workOrder->status === App\Enums\WorkOrderStatus::Open && $task->status !== App\Enums\WorkTaskStatus::SubmittedForReview && $task->status !== App\Enums\WorkTaskStatus::Completed)
                            <form method="POST" action="{{ route('staff.tasks.assign', $task) }}" class="rounded-xl border border-line bg-sand-50 p-4">@csrf<h3 class="text-sm font-semibold text-slate-800">Add or replace a worker</h3><label class="mt-3 block text-xs font-semibold text-slate-600">Approved worker<select name="worker_id" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal">@foreach ($eligibleWorkers->reject(fn ($worker) => $task->activeAssignments->contains('worker_id', $worker->id)) as $worker)<option value="{{ $worker->id }}">{{ $worker->name }}</option>@endforeach</select></label><label class="mt-3 block text-xs font-semibold text-slate-600">Assignment action<select name="replace_assignment_id" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"><option value="">Add this worker to the team</option>@foreach ($task->activeAssignments as $assignment)<option value="{{ $assignment->id }}">Replace {{ $assignment->worker->name }}</option>@endforeach</select></label><label class="mt-3 block text-xs font-semibold text-slate-600">Instructions for this worker<textarea name="assignment_instructions" rows="2" maxlength="2000" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"></textarea></label><label class="mt-3 block text-xs font-semibold text-slate-600">Reason for replacement <span class="font-normal">(required when replacing)</span><textarea name="reason" rows="2" maxlength="1000" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"></textarea></label><button class="mt-3 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-xs font-semibold text-forest-900 hover:bg-forest-50">Save team assignment</button></form>
                        @elseif ($task->activeAssignments->isEmpty() && $task->status === App\Enums\WorkTaskStatus::Queued)
                            <p class="rounded-xl bg-sand-50 p-4 text-xs leading-5 text-slate-500">No approved workers currently have an active membership in this service unit.</p>
                        @endif
                        @if ($task->status === App\Enums\WorkTaskStatus::SubmittedForReview)
                            @can('verify', $task)
                                <form method="POST" action="{{ route('staff.tasks.verify', $task) }}" class="rounded-xl border border-forest-100 bg-forest-50/60 p-4">@csrf @method('PUT')<h3 class="text-sm font-semibold text-forest-950">Verify submitted work</h3><label class="mt-3 block text-xs font-semibold text-slate-600">Decision<select name="decision" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"><option value="approved">Approve and complete task</option><option value="needs_rework">Request correction</option></select></label><label class="mt-3 block text-xs font-semibold text-slate-600">Correction details<textarea name="reason" rows="3" maxlength="1500" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal" placeholder="Required when requesting a correction."></textarea></label><button class="mt-3 w-full rounded-lg bg-forest-900 px-3 py-2.5 text-xs font-semibold text-white hover:bg-forest-800">Save verification</button></form>
                            @endcan
                        @endif
                    </div>
                </div>
                @php($completedAssignments = $task->assignments->where('status', App\Enums\TaskAssignmentStatus::Completed))
                @if ($completedAssignments->isNotEmpty())
                    <section class="mt-5 border-t border-line pt-4" aria-label="Verified worker feedback">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">Worker feedback</h3>
                        <div class="mt-3 space-y-3">
                            @foreach ($completedAssignments as $completedAssignment)
                                <div class="rounded-xl bg-sand-50 p-4">
                                    <p class="text-sm font-semibold text-slate-800">{{ $completedAssignment->worker->name }}</p>
                                    @if ($completedAssignment->workerRating)
                                        <p class="mt-1 text-xs leading-5 text-slate-600">Quality {{ $completedAssignment->workerRating->quality_rating }}/5 · Safety {{ $completedAssignment->workerRating->safety_rating }}/5 · Professionalism {{ $completedAssignment->workerRating->professionalism_rating }}/5</p>
                                        @if ($completedAssignment->workerRating->comment)<p class="mt-2 whitespace-pre-line text-xs leading-5 text-slate-600">{{ $completedAssignment->workerRating->comment }}</p>@endif
                                    @else
                                        @can('feedback.rate_workers')
                                            <form method="POST" action="{{ route('staff.tasks.ratings.store', [$task, $completedAssignment]) }}" class="mt-3 grid gap-3 sm:grid-cols-3">
                                                @csrf
                                                <label class="text-xs font-semibold text-slate-600">Quality<select name="quality_rating" required class="mt-1 block w-full rounded-lg border border-line bg-white px-2.5 py-2 text-sm font-normal">@for ($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ $rating }} / 5</option>@endfor</select></label>
                                                <label class="text-xs font-semibold text-slate-600">Safety<select name="safety_rating" required class="mt-1 block w-full rounded-lg border border-line bg-white px-2.5 py-2 text-sm font-normal">@for ($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ $rating }} / 5</option>@endfor</select></label>
                                                <label class="text-xs font-semibold text-slate-600">Professionalism<select name="professionalism_rating" required class="mt-1 block w-full rounded-lg border border-line bg-white px-2.5 py-2 text-sm font-normal">@for ($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ $rating }} / 5</option>@endfor</select></label>
                                                <label class="text-xs font-semibold text-slate-600 sm:col-span-3">Private staff note<textarea name="comment" rows="2" maxlength="2000" class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2 text-sm font-normal"></textarea></label>
                                                <div class="sm:col-span-3"><button class="rounded-lg border border-line bg-white px-3.5 py-2 text-xs font-semibold text-forest-900 hover:bg-forest-50">Record worker feedback</button></div>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </article>
        @endforeach
    </section>
@endsection
