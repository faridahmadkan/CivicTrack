@extends($isAdministrator ? 'layouts.admin' : 'layouts.staff')

@section('title', 'Report '.$report->public_reference)
@section('eyebrow', 'Report review and triage')

@section('content')
    <x-back-link href="{{ route('staff.reports.index') }}">Report queue</x-back-link>
    <div class="mt-5 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-5">
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="font-mono text-xs font-semibold text-slate-500">{{ $report->public_reference }}</p><h1 class="mt-2 text-2xl font-semibold tracking-tight text-forest-950">{{ $report->title }}</h1><p class="mt-2 text-sm text-slate-600">{{ $report->category->name }} · submitted {{ $report->submitted_at->format('M j, Y · g:i A') }}</p></div><span class="rounded-full bg-forest-50 px-3 py-1.5 text-xs font-semibold text-forest-900">{{ $report->status->label() }}</span></div>
                <div class="mt-5 grid gap-4 border-t border-line pt-5 sm:grid-cols-2">
                    <div><p class="text-[0.68rem] font-bold uppercase tracking-wide text-slate-500">Reporter</p><p class="mt-1 text-sm font-medium text-slate-800">{{ $report->reporter?->name ?? 'Guest citizen' }}</p><p class="mt-1 text-xs text-slate-500">{{ $report->reporter?->email ?? 'No registered account' }}</p></div>
                    <div><p class="text-[0.68rem] font-bold uppercase tracking-wide text-slate-500">Routed service unit</p><p class="mt-1 text-sm font-medium text-slate-800">{{ $report->routedUnit?->name ?? 'Unassigned triage queue' }}</p></div>
                </div>
                <h2 class="mt-5 text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Issue details</h2><p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $report->description }}</p>
                @include('staff.reports._sla')
                @include('staff.reports._reopen-requests')
                <div class="mt-5 border-t border-line pt-5"><h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Precise location · staff only</h2><p class="mt-2 text-sm text-slate-700">{{ $report->location?->address ?: 'Pinned on the map' }}</p>@if ($report->location)<p class="mt-1 font-mono text-xs text-slate-500">{{ $report->location->latitude }}, {{ $report->location->longitude }}</p>@endif</div>
                <div class="mt-5 border-t border-line pt-5"><h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Private attachments</h2><ul class="mt-2 space-y-2">@forelse ($report->attachments as $attachment)<li><a href="{{ route('staff.reports.attachments.download', [$report, $attachment]) }}" class="break-all text-sm font-medium text-forest-800 underline underline-offset-2">{{ $attachment->original_name }}</a></li>@empty<li class="text-sm text-slate-500">No supporting files.</li>@endforelse</ul></div>
                <div class="mt-5 border-t border-line pt-5"><h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Work orders</h2><ul class="mt-2 space-y-2">@forelse ($report->workOrders as $workOrder)<li><a href="{{ route('staff.work-orders.show', $workOrder) }}" class="text-sm font-semibold text-forest-800 underline underline-offset-2">{{ $workOrder->reference }} · {{ $workOrder->title }}</a></li>@empty<li class="text-sm text-slate-500">No field work has been planned.</li>@endforelse</ul>@if ($report->status === App\Enums\CivicReportStatus::Acknowledged)<a href="{{ route('staff.work-orders.create', $report) }}" class="mt-4 inline-flex rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Plan field work</a>@endif</div>
            </section>

            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-base font-semibold text-forest-950">Decision history</h2>
                <ol class="mt-4 space-y-4">@foreach ($report->statusEvents as $event)<li class="border-l-2 border-forest-100 pl-4"><p class="text-sm font-semibold text-slate-800">{{ $event->to_status?->label() ?? str($event->event_type)->headline() }}</p><p class="mt-1 text-xs text-slate-500">{{ $event->actor?->name ?? 'Guest citizen' }} · {{ $event->occurred_at->format('M j, Y · g:i A') }}</p>@if ($event->reason)<p class="mt-2 text-sm leading-6 text-slate-600">{{ $event->reason }}</p>@endif</li>@endforeach</ol>
                <div class="mt-6 border-t border-line pt-5"><h3 class="text-sm font-semibold text-slate-800">Staff notes and citizen updates</h3><div class="mt-3 space-y-3">@forelse ($report->comments as $comment)<article class="rounded-xl p-4 {{ $comment->visibility->value === 'internal' ? 'bg-sand-100' : 'bg-forest-50' }}"><div class="flex flex-wrap justify-between gap-2 text-xs text-slate-500"><span>{{ $comment->visibility->value === 'internal' ? 'Internal · ' : 'Public · ' }}{{ $comment->actor?->name ?? $comment->author_label ?? 'Guest reporter' }}</span><time>{{ $comment->created_at->format('M j, Y · g:i A') }}</time></div><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $comment->body }}</p></article>@empty<p class="text-sm text-slate-500">No comments have been added.</p>@endforelse</div></div>
                @can('addInternalComment', $report)
                    <form method="POST" action="{{ route('staff.reports.comments.store', $report) }}" class="mt-5 border-t border-line pt-5">@csrf<label class="block text-sm font-semibold text-slate-800">Add an internal note<textarea name="body" rows="3" maxlength="3000" required class="mt-2 w-full rounded-xl border border-line px-3.5 py-3 text-sm font-normal" placeholder="Only authorized staff can see this note."></textarea></label><button class="mt-3 rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-semibold text-forest-900 hover:bg-forest-50">Save internal note</button></form>
                @endcan
            </section>
        </div>

        <aside class="space-y-5">
            @if ($report->status === App\Enums\CivicReportStatus::Disputed)
                @can('triage', $report)
                    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-amber-950">Review citizen dispute</h2>
                        <p class="mt-1 text-xs leading-5 text-amber-900">The previous outcome was {{ $report->statusEvents->where('event_type', 'dispute_submitted')->last()?->from_status?->label() ?? 'closed' }}. Reopening starts a new service cycle.</p>
                        <form method="POST" action="{{ route('staff.reports.disputes.review', $report) }}" class="mt-4 space-y-3">@csrf @method('PUT')
                            <label class="block text-xs font-semibold text-amber-950">Decision<select name="decision" required class="mt-1.5 w-full rounded-lg border border-amber-200 bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="reopen">Reopen and resume service</option><option value="uphold">Uphold the previous outcome</option></select></label>
                            <label class="block text-xs font-semibold text-amber-950">Review reason<textarea name="reason" rows="3" minlength="3" maxlength="1000" required class="mt-1.5 w-full rounded-lg border border-amber-200 bg-white px-3 py-2.5 text-sm font-normal"></textarea></label>
                            <button class="w-full rounded-lg bg-amber-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-800">Record dispute review</button>
                        </form>
                    </section>
                @endcan
            @endif
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-forest-950">Related reports</h2>
                <ul class="mt-3 space-y-3">
                    @forelse ($reportRelationships as $item)
                        <li class="rounded-xl bg-sand-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                            <a href="{{ route('staff.reports.show', $item['report']) }}" class="mt-1 block text-sm font-semibold text-forest-800 underline underline-offset-2">{{ $item['report']->public_reference }} · {{ $item['report']->title }}</a>
                            <p class="mt-1 text-xs leading-5 text-slate-600">{{ $item['relationship']->reason }}</p>
                            <p class="mt-1 text-[0.7rem] text-slate-500">Recorded by {{ $item['relationship']->creator?->name ?? 'Former staff member' }} · {{ $item['relationship']->created_at->format('M j, Y · g:i A') }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">No report links have been reviewed.</li>
                    @endforelse
                </ul>

                @can('triage', $report)
                    <div class="mt-5 border-t border-line pt-4">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">Possible duplicates</h3>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Suggestions use category, title, and location. They do not change report status.</p>
                        <ul class="mt-3 space-y-3">
                            @forelse ($duplicateSuggestions as $suggestion)
                                <li class="rounded-xl border border-line p-3">
                                    <a href="{{ route('staff.reports.show', $suggestion['report']) }}" class="text-sm font-semibold text-forest-800 underline underline-offset-2">{{ $suggestion['report']->public_reference }} · {{ $suggestion['report']->title }}</a>
                                    <p class="mt-1 text-xs text-slate-500">{{ $suggestion['match_reason'] }}</p>
                                    <details class="mt-3">
                                        <summary class="cursor-pointer text-xs font-semibold text-forest-800">Review link or duplicate</summary>
                                        <form method="POST" action="{{ route('staff.reports.related.store', [$report, $suggestion['report']]) }}" class="mt-3 space-y-2">@csrf
                                            <label class="block text-xs font-medium text-slate-600">Why are these reports related?<textarea name="reason" minlength="3" maxlength="1000" required rows="2" class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-xs"></textarea></label>
                                            <button class="rounded-lg border border-line px-3 py-2 text-xs font-semibold text-forest-900 hover:bg-forest-50">Link as related</button>
                                        </form>
                                        <form method="POST" action="{{ route('staff.reports.duplicates.store', [$report, $suggestion['report']]) }}" class="mt-3 space-y-2">@csrf
                                            <label class="block text-xs font-medium text-slate-600">Why is this a duplicate?<textarea name="reason" minlength="3" maxlength="1000" required rows="2" class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-xs"></textarea></label>
                                            <button class="rounded-lg bg-forest-900 px-3 py-2 text-xs font-semibold text-white hover:bg-forest-800">Mark this report duplicate</button>
                                        </form>
                                        <form method="POST" action="{{ route('staff.reports.duplicates.merge', [$report, $suggestion['report']]) }}" class="mt-3 space-y-2" onsubmit="return confirm('Keep both reports and their histories, and mark this report as merged into the selected report?')">@csrf
                                            <label class="block text-xs font-medium text-slate-600">Why should this report be merged?<textarea name="reason" minlength="3" maxlength="1000" required rows="2" class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-xs"></textarea></label>
                                            <button class="rounded-lg border border-amber-300 px-3 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-50">Merge into selected report</button>
                                        </form>
                                    </details>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">No similar reports were found in this service category.</li>
                            @endforelse
                        </ul>
                    </div>
                @endcan
            </section>
            @can('triage', $report)
                <section class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-forest-950">Record triage decision</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Acknowledge the report, request more information, or record why it cannot proceed.</p>
                    <form method="POST" action="{{ route('staff.reports.triage', $report) }}" class="mt-4 space-y-4">@csrf @method('PUT')
                        <label class="block text-xs font-semibold text-slate-600">Decision<select name="status" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="under_review">Under review</option><option value="acknowledged">Acknowledge report</option><option value="needs_information">Request information</option><option value="rejected">Close without action</option><option value="duplicate">Mark as duplicate</option></select></label>
                        <label class="block text-xs font-semibold text-slate-600">Priority<select name="priority" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800">@foreach (App\Enums\CivicReportPriority::cases() as $priority)<option value="{{ $priority->value }}" @selected($report->priority === $priority)>{{ $priority->label() }}</option>@endforeach</select></label>
                        <label class="block text-xs font-semibold text-slate-600">Reason or message<textarea name="reason" rows="4" maxlength="1000" class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal" placeholder="Required when requesting information or closing without action."></textarea></label>
                        <label class="block text-xs font-semibold text-slate-600">Public listing<select name="is_public" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="0" @selected(! $report->is_public)>Keep private</option><option value="1" @selected($report->is_public)>Allow generalized public location</option></select></label>
                        <button class="w-full rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Save triage decision</button>
                    </form>
                </section>
            @endcan
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm"><h2 class="text-sm font-semibold text-forest-950">Routing record</h2><ol class="mt-3 space-y-3">@foreach ($report->routingDecisions as $decision)<li><p class="text-xs font-semibold text-slate-700">{{ str($decision->source)->replace('_', ' ')->headline() }}</p><p class="mt-1 text-xs leading-5 text-slate-500">{{ $decision->organizationUnit?->name ?? 'Unassigned triage queue' }} · {{ $decision->reason }}</p></li>@endforeach</ol></section>
        </aside>
    </div>
@endsection
