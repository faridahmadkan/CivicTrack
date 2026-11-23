@php
    $showRoute = $guestAccess ? 'public.reports.track.show' : 'citizen.reports.show';
    $commentRoute = $guestAccess ? 'public.reports.track.comments.store' : 'citizen.reports.comments.store';
    $attachmentRoute = $guestAccess ? 'public.reports.track.attachments.download' : 'citizen.reports.attachments.download';
    $pendingReopenRequest = $report->reopenRequests->firstWhere('status', App\Enums\ReportReopenStatus::Pending);
    $reopenDeadline = $report->resolved_at?->copy()->addDays((int) config('civictrack.feedback.reopen_window_days', 30));
    $disputeDeadline = ($report->resolved_at ?? $report->closed_at)?->copy()->addDays((int) config('civictrack.feedback.reopen_window_days', 30));
    $timelineItems = $report->statusEvents
        ->map(static fn ($event): object => (object) [
            'to_status' => $event->to_status,
            'event_type' => $event->event_type,
            'reason' => $event->reason,
            'occurred_at' => $event->occurred_at,
        ])
        ->concat($report->comments->map(static fn ($comment): object => (object) [
            'to_status' => null,
            'event_type' => 'resident_update',
            'reason' => $comment->body,
            'occurred_at' => $comment->created_at,
        ]))
        ->sortBy(static fn (object $item): int => $item->occurred_at->getTimestamp())
        ->values();
@endphp

<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_19rem]">
    <div class="space-y-6">
        <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="font-mono text-xs font-semibold tracking-wide text-slate-500">{{ $report->public_reference }}</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-forest-950">{{ $report->title }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ $report->category->name }} · submitted {{ $report->submitted_at->format('M j, Y · g:i A') }}</p>
                </div>
                <x-status-badge :status="$report->status" class="px-3 py-1.5" />
            </div>
            <div class="mt-6 border-t border-line pt-5">
                <h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Issue details</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $report->description }}</p>
            </div>
            <div class="mt-5 border-t border-line pt-5">
                <h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Reported location</h2>
                @if ($report->location)
                    <p class="mt-2 text-sm text-slate-700">{{ $report->location->address ?: 'Pinned on the map' }}</p>
                    <p class="mt-1 font-mono text-xs text-slate-500">{{ number_format((float) $report->location->latitude, 6) }}, {{ number_format((float) $report->location->longitude, 6) }}</p>
                @else
                    <p class="mt-2 text-sm text-slate-500">No location was included.</p>
                @endif
            </div>
        </section>

        <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7" aria-labelledby="report-history-heading">
            <h2 id="report-history-heading" class="text-base font-semibold text-forest-950">Progress timeline</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">Status changes and public updates for this report.</p>
            <ol class="mt-5 space-y-0" aria-label="Citizen-visible report progress">
                @forelse ($timelineItems as $event)
                    <li class="relative flex gap-4 pb-6 last:pb-0">
                        @unless ($loop->last)<span class="absolute bottom-0 left-[0.34rem] top-3 w-px bg-line" aria-hidden="true"></span>@endunless
                        <span class="relative z-10 mt-1 size-3 shrink-0 rounded-full {{ $event->to_status ? 'bg-forest-700 ring-4 ring-forest-50' : 'bg-slate-400 ring-4 ring-slate-100' }}" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">{{ $event->to_status?->label() ?? str($event->event_type)->headline() }}</p>
                            <time class="mt-1 block text-xs text-slate-500" datetime="{{ $event->occurred_at->toIso8601String() }}">{{ $event->occurred_at->format('M j, Y · g:i A') }}</time>
                            @if ($event->reason)<p class="mt-2 text-sm leading-6 text-slate-600">{{ $event->reason }}</p>@endif
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">No public updates have been recorded yet.</li>
                @endforelse
            </ol>
        </section>

        <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7" aria-labelledby="report-comments-heading">
            <h2 id="report-comments-heading" class="text-base font-semibold text-forest-950">Report follow-up</h2>
            @unless ($report->status->isTerminal())
                <form method="POST" action="{{ route($commentRoute, $report) }}" class="mt-5 border-t border-line pt-5">
                    @csrf
                    <label class="block text-sm font-semibold text-slate-800">Add an update
                        <textarea name="body" rows="3" maxlength="3000" required class="mt-2 w-full rounded-xl border border-line px-3.5 py-3 text-sm font-normal" placeholder="Share a clarification or answer a service team's question.">{{ old('body') }}</textarea>
                    </label>
                    <button class="button-secondary mt-3">Send update</button>
                </form>
            @endunless
            @if ($report->serviceFeedback)
                <div class="mt-5 border-t border-line pt-5">
                    <h3 class="text-sm font-semibold text-slate-800">Your service feedback</h3>
                    <p class="mt-2 text-sm font-semibold text-forest-800" aria-label="{{ $report->serviceFeedback->rating }} out of 5 stars">{{ str_repeat('★', $report->serviceFeedback->rating) }}{{ str_repeat('☆', 5 - $report->serviceFeedback->rating) }} <span class="text-slate-600">{{ $report->serviceFeedback->rating }} out of 5</span></p>
                    @if ($report->serviceFeedback->comment)<p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $report->serviceFeedback->comment }}</p>@endif
                </div>
            @elseif ($report->status === App\Enums\CivicReportStatus::Resolved)
                <form method="POST" action="{{ route('public.reports.feedback.store', $report) }}" class="mt-5 border-t border-line pt-5">
                    @csrf
                    <h3 class="text-sm font-semibold text-slate-800">Rate this service response</h3>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Your rating is linked to this report and helps the service team review resident experience.</p>
                    <label class="mt-3 block text-sm font-semibold text-slate-700">Overall rating
                        <select name="rating" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal">
                            <option value="">Choose a rating</option>
                            <option value="5" @selected(old('rating') == '5')>5 — Excellent</option>
                            <option value="4" @selected(old('rating') == '4')>4 — Good</option>
                            <option value="3" @selected(old('rating') == '3')>3 — Fair</option>
                            <option value="2" @selected(old('rating') == '2')>2 — Poor</option>
                            <option value="1" @selected(old('rating') == '1')>1 — Very poor</option>
                        </select>
                    </label>
                    <label class="mt-3 block text-sm font-semibold text-slate-700">Comment <span class="font-normal text-slate-500">(optional)</span>
                        <textarea name="comment" rows="3" maxlength="2000" class="mt-1.5 w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal">{{ old('comment') }}</textarea>
                    </label>
                    <button class="button-primary mt-3">Submit feedback</button>
                </form>
            @endif
        </section>
    </div>

    <aside class="space-y-5">
        <section class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-forest-950">Current service status</h2>
            <p class="mt-3"><x-status-badge :status="$report->status" /></p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Priority: {{ $report->priority->label() }}</p>
        </section>
        @if ($report->slaInstance && $report->slaInstance->state !== App\Enums\ReportSlaState::NotApplicable)
            @php($sla = $report->slaInstance)
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm" aria-labelledby="service-target-heading">
                <h2 id="service-target-heading" class="text-sm font-semibold text-forest-950">Service targets</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $sla->policy_name_snapshot }} · policy version {{ $sla->policy_version_snapshot }}</p>
                <dl class="mt-4 space-y-4">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">First response</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">
                            @if ($sla->first_response_at)
                                {{ $sla->first_response_breached_at ? 'Responded after target' : 'Responded within target' }} · {{ $sla->first_response_at->format('M j, Y · g:i A') }}
                            @elseif (now()->greaterThan($sla->first_response_due_at))
                                Overdue · target {{ $sla->first_response_due_at->format('M j, Y · g:i A') }}
                            @else
                                Due {{ $sla->first_response_due_at->format('M j, Y · g:i A') }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Resolution</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">
                            @if ($sla->resolved_at)
                                {{ $sla->resolution_breached_at ? 'Resolved after target' : 'Resolved within target' }} · {{ $sla->resolved_at->format('M j, Y · g:i A') }}
                            @elseif (now()->greaterThan($sla->resolution_due_at))
                                Overdue · target {{ $sla->resolution_due_at->format('M j, Y · g:i A') }}
                            @else
                                Due {{ $sla->resolution_due_at->format('M j, Y · g:i A') }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>
        @else
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm" aria-labelledby="service-target-heading">
                <h2 id="service-target-heading" class="text-sm font-semibold text-forest-950">Service targets</h2>
                <p class="mt-2 text-xs leading-5 text-slate-500">No response or resolution target was configured for this report when it was submitted.</p>
            </section>
        @endif
        @if ($report->reopenRequests->isNotEmpty())
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm" aria-labelledby="reopen-history-heading">
                <h2 id="reopen-history-heading" class="text-sm font-semibold text-forest-950">Reopen request history</h2>
                <ol class="mt-3 space-y-3">
                    @foreach ($report->reopenRequests as $reopenRequest)
                        <li class="border-l-2 border-forest-100 pl-3">
                            <p class="text-xs font-semibold text-slate-800">{{ str($reopenRequest->status->value)->headline() }} · {{ $reopenRequest->requested_at->format('M j, Y') }}</p>
                            <p class="mt-1 whitespace-pre-line text-xs leading-5 text-slate-600">{{ $reopenRequest->reason }}</p>
                            @if ($reopenRequest->decision_reason)<p class="mt-1 text-xs leading-5 text-slate-500">Staff decision: {{ $reopenRequest->decision_reason }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif
        @if ($pendingReopenRequest)
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5" role="status">
                <h2 class="text-sm font-semibold text-amber-950">Reopen request under review</h2>
                <p class="mt-2 text-xs leading-5 text-amber-900">A service team member will review the reason you provided. The report remains resolved until the request is approved.</p>
            </section>
        @elseif ($report->status === App\Enums\CivicReportStatus::Resolved && $reopenDeadline && now()->lessThanOrEqualTo($reopenDeadline))
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm" aria-labelledby="request-reopen-heading">
                <h2 id="request-reopen-heading" class="text-sm font-semibold text-forest-950">Still needs attention?</h2>
                <p class="mt-2 text-xs leading-5 text-slate-500">Ask the service team to review the resolution by {{ $reopenDeadline->format('M j, Y') }}. A staff member must approve the request before work resumes.</p>
                <form method="POST" action="{{ route('public.reports.reopen-requests.store', $report) }}" class="mt-3">
                    @csrf
                    <label class="block text-xs font-semibold text-slate-700">Why should this report be reopened?
                        <textarea name="reason" rows="3" maxlength="2000" required class="mt-1.5 w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal">{{ old('reason') }}</textarea>
                    </label>
                    <button class="button-secondary mt-3">Request review</button>
                </form>
            </section>
        @endif
        @if (in_array($report->status, [App\Enums\CivicReportStatus::Submitted, App\Enums\CivicReportStatus::UnderReview, App\Enums\CivicReportStatus::NeedsInformation, App\Enums\CivicReportStatus::Acknowledged], true) && $report->work_orders_count === 0)
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm" aria-labelledby="cancel-report-heading">
                <h2 id="cancel-report-heading" class="text-sm font-semibold text-forest-950">Withdraw this report</h2>
                <p class="mt-2 text-xs leading-5 text-slate-500">You can cancel before field work is planned. The report and its history will remain on record.</p>
                <form method="POST" action="{{ route('public.reports.cancel', $report) }}" class="mt-3" onsubmit="return confirm('Cancel this report? Its history will be retained.')">
                    @csrf
                    <label class="block text-xs font-semibold text-slate-700">Reason for cancellation
                        <textarea name="reason" rows="3" minlength="3" maxlength="1000" required class="mt-1.5 w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal">{{ old('reason') }}</textarea>
                    </label>
                    <button class="button-danger mt-3">Cancel report</button>
                </form>
            </section>
        @endif
        @if (in_array($report->status, [App\Enums\CivicReportStatus::Resolved, App\Enums\CivicReportStatus::Closed, App\Enums\CivicReportStatus::Rejected, App\Enums\CivicReportStatus::Duplicate, App\Enums\CivicReportStatus::Cancelled], true) && $disputeDeadline && now()->lessThanOrEqualTo($disputeDeadline))
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5" aria-labelledby="dispute-report-heading">
                <h2 id="dispute-report-heading" class="text-sm font-semibold text-amber-950">Disagree with this outcome?</h2>
                <p class="mt-2 text-xs leading-5 text-amber-900">Ask the service team to review the closed outcome by {{ $disputeDeadline->format('M j, Y') }}.</p>
                <form method="POST" action="{{ route('public.reports.disputes.store', $report) }}" class="mt-3" onsubmit="return confirm('Send this outcome for staff review?')">
                    @csrf
                    <label class="block text-xs font-semibold text-amber-950">Why should the outcome be reviewed?
                        <textarea name="reason" rows="3" minlength="3" maxlength="2000" required class="mt-1.5 w-full rounded-xl border border-amber-200 bg-white px-3 py-2.5 text-sm font-normal">{{ old('reason') }}</textarea>
                    </label>
                    <button class="button-danger mt-3">Request outcome review</button>
                </form>
            </section>
        @endif
        <section class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-forest-950">Supporting files</h2>
            <ul class="mt-3 space-y-2">
                @forelse ($report->attachments as $attachment)
                    <li><a href="{{ route($attachmentRoute, [$report, $attachment]) }}" class="break-all text-sm font-medium text-forest-800 underline decoration-forest-200 underline-offset-2">{{ $attachment->original_name }}</a></li>
                @empty
                    <li class="text-sm text-slate-500">No files were added.</li>
                @endforelse
            </ul>
        </section>
        @if ($guestAccess)
            <a href="{{ route('public.home') }}" class="block rounded-xl border border-line bg-white p-4 text-sm font-semibold text-forest-900 hover:bg-forest-50">Return to CivicTrack</a>
        @else
            <a href="{{ route('citizen.reports.index') }}" class="block rounded-xl border border-line bg-white p-4 text-sm font-semibold text-forest-900 hover:bg-forest-50">All my reports</a>
        @endif
    </aside>
</div>
