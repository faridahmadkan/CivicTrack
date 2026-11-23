@extends(auth()->user()->can('platform.access') ? 'layouts.admin' : 'layouts.staff')

@section('title', 'Plan work')
@section('eyebrow', 'Create a field work order')

@section('content')
    <x-back-link href="{{ route('staff.reports.show', $report) }}">Return to report</x-back-link>
    <div class="mt-5 max-w-4xl">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">{{ $report->public_reference }} · {{ $report->category->name }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Plan field work</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Create one work order with the tasks needed to address “{{ $report->title }}”. Each task will be assigned to an approved worker in {{ $report->routedUnit?->name ?? 'the routed service unit' }}.</p>
    </div>

    <form method="POST" action="{{ route('staff.work-orders.store', $report) }}" class="mt-7 max-w-4xl space-y-6">
        @csrf
        <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-base font-semibold text-forest-950">Work order details</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">Work order title
                    <input name="title" value="{{ old('title', 'Service response for '.$report->public_reference) }}" maxlength="160" required class="mt-2 w-full rounded-xl border border-line px-3.5 py-3 text-sm font-normal">
                    @error('title')<span class="mt-1 block text-xs text-coral-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">Instructions for the team
                    <textarea name="instructions" rows="3" maxlength="4000" class="mt-2 w-full rounded-xl border border-line px-3.5 py-3 text-sm font-normal">{{ old('instructions') }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-slate-800">Target completion date <span class="font-normal text-slate-500">(optional)</span>
                    <input type="datetime-local" name="due_at" value="{{ old('due_at') }}" class="mt-2 block rounded-xl border border-line px-3.5 py-3 text-sm font-normal">
                </label>
            </div>
        </section>

        <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div><h2 class="text-base font-semibold text-forest-950">Tasks</h2><p class="mt-1 text-sm text-slate-600">Break the response into steps workers can complete and staff can verify independently.</p></div>
                <button type="button" id="add-work-task" class="rounded-lg border border-line bg-white px-3.5 py-2.5 text-sm font-semibold text-forest-900 hover:bg-forest-50">Add another task</button>
            </div>
            <div id="work-task-list" class="mt-5 space-y-4">
                <article data-task-row class="rounded-xl border border-line bg-sand-50 p-4 sm:p-5">
                    <div class="mb-4 flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-[0.15em] text-slate-500">Task 1</p><span class="text-xs text-slate-500">Required</span></div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">Task title
                            <input name="tasks[0][title]" data-task-title maxlength="160" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal" placeholder="Inspect and document the damaged crossing sign">
                        </label>
                        <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">Instructions
                            <textarea name="tasks[0][instructions]" rows="3" maxlength="3000" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal" placeholder="Describe the expected work and any safety requirements."></textarea>
                        </label>
                        <label class="block text-sm font-semibold text-slate-800">Priority
                            <select name="tasks[0][priority]" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"><option value="normal">Normal</option><option value="low">Low</option><option value="high">High</option><option value="urgent">Urgent</option></select>
                        </label>
                        <label class="block text-sm font-semibold text-slate-800">Task deadline <span class="font-normal text-slate-500">(optional)</span>
                            <input type="datetime-local" name="tasks[0][due_at]" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal">
                        </label>
                        <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">Prerequisites <span class="font-normal text-slate-500">(optional)</span>
                            <select name="tasks[0][depends_on][]" data-task-dependencies multiple class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal" aria-describedby="dependency-help"></select>
                            <span id="dependency-help" class="mt-1 block text-xs font-normal text-slate-500">Tasks can start after their selected prerequisite steps are verified complete.</span>
                        </label>
                    </div>
                </article>
            </div>
            <p id="task-limit-message" class="mt-3 hidden text-xs text-slate-500">A work order can contain up to 25 tasks.</p>
            <template id="work-task-template">
                <article data-task-row class="rounded-xl border border-line bg-sand-50 p-4 sm:p-5">
                    <div class="mb-4 flex items-center justify-between"><p data-task-label class="text-xs font-bold uppercase tracking-[0.15em] text-slate-500"></p><span class="text-xs text-slate-500">Required</span></div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">Task title<input name="tasks[__INDEX__][title]" data-task-title maxlength="160" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"></label>
                        <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">Instructions<textarea name="tasks[__INDEX__][instructions]" rows="3" maxlength="3000" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"></textarea></label>
                        <label class="block text-sm font-semibold text-slate-800">Priority<select name="tasks[__INDEX__][priority]" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"><option value="normal">Normal</option><option value="low">Low</option><option value="high">High</option><option value="urgent">Urgent</option></select></label>
                        <label class="block text-sm font-semibold text-slate-800">Task deadline <span class="font-normal text-slate-500">(optional)</span><input type="datetime-local" name="tasks[__INDEX__][due_at]" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"></label>
                        <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">Prerequisites <span class="font-normal text-slate-500">(optional)</span><select name="tasks[__INDEX__][depends_on][]" data-task-dependencies multiple class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"></select></label>
                    </div>
                </article>
            </template>
            @error('tasks')<span class="mt-3 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
            @error('tasks.*.depends_on')<span class="mt-3 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3"><a href="{{ route('staff.reports.show', $report) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a><button class="rounded-lg bg-forest-900 px-5 py-3 text-sm font-semibold text-white hover:bg-forest-800">Create work order</button></div>
    </form>
@endsection
