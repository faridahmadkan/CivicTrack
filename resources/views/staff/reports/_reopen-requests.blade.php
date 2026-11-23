@if ($report->reopenRequests->isNotEmpty())
    <section class="mt-5 border-t border-line pt-5" aria-labelledby="reopen-review-heading">
        <h2 id="reopen-review-heading" class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Citizen resolution review</h2>
        <div class="mt-3 space-y-3">
            @foreach ($report->reopenRequests as $reopenRequest)
                <article class="rounded-xl border border-line bg-sand-50 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ $reopenRequest->request_number }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $reopenRequest->requester?->name ?? 'Guest citizen' }} · {{ $reopenRequest->requested_at->format('M j, Y · g:i A') }}</p>
                        </div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-700">{{ str($reopenRequest->status->value)->headline() }}</span>
                    </div>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $reopenRequest->reason }}</p>
                    @if ($reopenRequest->decision_reason)
                        <p class="mt-2 text-xs leading-5 text-slate-500">Decision: {{ $reopenRequest->decision_reason }} · {{ $reopenRequest->reviewer?->name ?? 'Staff' }}</p>
                    @endif
                    @if ($reopenRequest->status === App\Enums\ReportReopenStatus::Pending)
                        @can('triage', $report)
                            <form method="POST" action="{{ route('staff.reopen-requests.review', $reopenRequest) }}" class="mt-4 border-t border-line pt-4">
                                @csrf
                                @method('PUT')
                                <label class="block text-xs font-semibold text-slate-600">Decision
                                    <select name="decision" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal">
                                        <option value="approved">Approve and reopen report</option>
                                        <option value="rejected">Decline request</option>
                                    </select>
                                </label>
                                <label class="mt-3 block text-xs font-semibold text-slate-600">Decision reason <span class="font-normal">(required when declining)</span>
                                    <textarea name="decision_reason" rows="2" maxlength="1000" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal"></textarea>
                                </label>
                                <button class="mt-3 rounded-lg bg-forest-900 px-4 py-2.5 text-xs font-semibold text-white hover:bg-forest-800">Record decision</button>
                            </form>
                        @endcan
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endif
