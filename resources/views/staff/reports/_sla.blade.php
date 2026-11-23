<div class="mt-5 border-t border-line pt-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Service level agreement</h2>
            @if ($report->slaInstance && $report->slaInstance->policy_name_snapshot)
                <p class="mt-1 text-sm font-semibold text-slate-800">{{ $report->slaInstance->policy_name_snapshot }} · version {{ $report->slaInstance->policy_version_snapshot }}</p>
            @else
                <p class="mt-1 text-sm text-slate-600">No matching service level policy was active when this report was submitted.</p>
            @endif
        </div>
        @if ($report->slaInstance)
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $report->slaInstance->state === App\Enums\ReportSlaState::Breached ? 'bg-red-50 text-red-800' : ($report->slaInstance->state === App\Enums\ReportSlaState::Met ? 'bg-forest-50 text-forest-800' : 'bg-amber-50 text-amber-800') }}">
                {{ str($report->slaInstance->state->value)->headline() }} · {{ $report->slaInstance->escalation_count }} escalations
            </span>
        @endif
    </div>

    @if ($report->slaInstance && $report->slaInstance->state !== App\Enums\ReportSlaState::NotApplicable)
        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl bg-sand-50 p-3">
                <dt class="text-[0.68rem] font-semibold uppercase tracking-wide text-slate-500">First response due</dt>
                <dd class="mt-1 text-sm font-medium text-slate-800">{{ $report->slaInstance->first_response_due_at->format('M j, Y · g:i A') }}</dd>
                <dd class="mt-1 text-xs text-slate-500">{{ $report->slaInstance->first_response_at ? 'Recorded '.$report->slaInstance->first_response_at->format('M j, Y · g:i A') : 'Awaiting first response' }}</dd>
            </div>
            <div class="rounded-xl bg-sand-50 p-3">
                <dt class="text-[0.68rem] font-semibold uppercase tracking-wide text-slate-500">Resolution due</dt>
                <dd class="mt-1 text-sm font-medium text-slate-800">{{ $report->slaInstance->resolution_due_at->format('M j, Y · g:i A') }}</dd>
                <dd class="mt-1 text-xs text-slate-500">{{ $report->slaInstance->resolved_at ? 'Closed '.$report->slaInstance->resolved_at->format('M j, Y · g:i A') : 'Report remains open' }}</dd>
            </div>
        </dl>
        <ol class="mt-4 space-y-2" aria-label="SLA event history">
            @forelse ($report->slaInstance->events as $slaEvent)
                <li class="border-l-2 border-amber-200 pl-3">
                    <p class="text-xs font-semibold text-slate-700">{{ str($slaEvent->event_type)->replace('_', ' ')->headline() }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $slaEvent->details }} · {{ $slaEvent->actor?->name ?? 'CivicTrack system' }} · {{ $slaEvent->occurred_at->format('M j, Y · g:i A') }}</p>
                </li>
            @empty
                <li class="text-xs text-slate-500">No SLA events recorded.</li>
            @endforelse
        </ol>
    @elseif ($report->slaInstance)
        <p class="mt-4 text-sm leading-6 text-slate-600">No response or resolution target was configured for this report cycle.</p>
    @endif
</div>
