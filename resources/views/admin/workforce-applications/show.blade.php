@extends('layouts.admin')

@section('title', 'Review '.$application->application_number)
@section('eyebrow', 'Application review')

@section('content')
    <div class="max-w-5xl">
        <a href="{{ route('admin.workforce-applications.index', ['type' => $application->application_type->value]) }}" class="text-sm font-semibold text-forest-800 hover:text-forest-950">← Application queue</a>
        <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">{{ ucfirst($application->application_type->value) }} application · {{ $application->application_number }}</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">{{ $application->position_title }}</h1><p class="mt-2 text-sm text-slate-600">Submitted {{ $application->submitted_at->format('M j, Y · H:i') }}</p></div>
            <span class="w-fit rounded-full bg-sand-100 px-3 py-1.5 text-xs font-semibold text-slate-700">{{ $application->status->label() }}</span>
        </div>

        <div class="mt-7 grid gap-5 lg:grid-cols-[1fr_21rem]">
            <div class="space-y-5">
                <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-sm font-semibold text-slate-900">Applicant and requested unit</h2>
                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2"><div><dt class="text-xs font-semibold text-slate-500">Applicant</dt><dd class="mt-1 font-medium text-slate-800">{{ $application->user->name }}</dd></div><div><dt class="text-xs font-semibold text-slate-500">Verified email</dt><dd class="mt-1 break-all font-medium text-slate-800">{{ $application->user->email }}</dd></div><div><dt class="text-xs font-semibold text-slate-500">Requested position</dt><dd class="mt-1 text-slate-800">{{ $application->position_title }}</dd></div><div><dt class="text-xs font-semibold text-slate-500">Organization unit</dt><dd class="mt-1 text-slate-800">{{ $application->requestedUnit?->name ?? 'Unit no longer available' }}</dd></div></dl>
                    <div class="mt-5 grid gap-4 border-t border-line pt-5 sm:grid-cols-2"><div><h3 class="text-xs font-semibold text-slate-500">Relevant experience</h3><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $application->experience_summary ?: 'Not provided' }}</p></div><div><h3 class="text-xs font-semibold text-slate-500">Qualifications and skills</h3><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $application->qualification_summary ?: 'Not provided' }}</p></div></div>
                    @if ($application->additional_information)<div class="mt-5 border-t border-line pt-5"><h3 class="text-xs font-semibold text-slate-500">Additional information from applicant</h3><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $application->additional_information }}</p></div>@endif
                </section>

                <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-sm font-semibold text-slate-900">Private supporting documents</h2>
                    @if ($application->documents->isEmpty())<p class="mt-2 text-sm text-slate-500">No documents were attached.</p>@else<div class="mt-3 divide-y divide-line">@foreach ($application->documents as $document)<div class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"><div><p class="text-sm font-medium text-slate-800">{{ $document->original_name }}</p><p class="mt-1 text-xs text-slate-500">{{ strtoupper(pathinfo($document->original_name, PATHINFO_EXTENSION)) }} · {{ \Illuminate\Support\Number::fileSize($document->size_bytes) }} · uploaded {{ $document->created_at->format('M j, Y') }}</p></div><a href="{{ route('admin.workforce-application-documents.download', $document) }}" class="rounded-lg border border-line px-3 py-2 text-xs font-semibold text-forest-800 transition hover:bg-forest-50">Download privately</a></div>@endforeach</div>@endif
                </section>

                <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-sm font-semibold text-slate-900">Application history</h2>
                    <ol class="mt-5 space-y-4">@foreach ($application->events as $event)<li class="flex gap-3"><span class="mt-1.5 size-2 shrink-0 rounded-full bg-forest-600"></span><div><p class="text-sm font-semibold text-slate-800">{{ $event->to_status === App\Enums\WorkforceApplicationStatus::Submitted->value ? ($event->event_type === 'information_provided' ? 'Additional information submitted' : 'Application submitted') : App\Enums\WorkforceApplicationStatus::from($event->to_status)->label() }}</p><p class="mt-1 text-xs text-slate-500">{{ $event->occurred_at->format('M j, Y · H:i') }}</p>@if ($event->reason)<p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $event->reason }}</p>@endif</div></li>@endforeach</ol>
                </section>
            </div>

            <aside class="h-fit rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-sm font-semibold text-slate-900">Review decision</h2>
                @if (in_array($application->status, [App\Enums\WorkforceApplicationStatus::Submitted, App\Enums\WorkforceApplicationStatus::UnderReview], true))
                    <p class="mt-2 text-xs leading-5 text-slate-500">Approval grants only this application’s role and requested unit membership. The worker role remains separate from staff permissions.</p>
                    <form method="POST" action="{{ route('admin.workforce-applications.review', $application) }}" class="mt-5 space-y-4">
                        @csrf @method('PUT')
                        <div><label for="decision" class="mb-1.5 block text-xs font-semibold text-slate-700">Decision</label><select id="decision" name="status" required class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100"><option value="under_review">Mark under review</option><option value="information_requested">Request more information</option><option value="approved">Approve application</option><option value="rejected">Do not approve</option></select>@error('status')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                        <div><label for="reason" class="mb-1.5 block text-xs font-semibold text-slate-700">Reason <span class="font-normal text-slate-500">(required for information request or rejection)</span></label><textarea id="reason" name="reason" rows="5" maxlength="2000" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">{{ old('reason') }}</textarea>@error('reason')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                        <button type="submit" class="w-full rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-800">Save decision</button>
                    </form>
                @else
                    <p class="mt-2 text-sm leading-6 text-slate-600">This application has a final decision and cannot be reviewed again.</p>
                    @if ($application->decision_reason)<p class="mt-4 rounded-xl bg-sand-50 p-3 text-sm leading-6 text-slate-700">{{ $application->decision_reason }}</p>@endif
                    <dl class="mt-4 space-y-2 border-t border-line pt-4 text-xs"><div class="flex justify-between gap-3"><dt class="text-slate-500">Reviewed</dt><dd class="text-right font-medium text-slate-700">{{ $application->reviewed_at?->format('M j, Y · H:i') ?? '—' }}</dd></div><div class="flex justify-between gap-3"><dt class="text-slate-500">Reviewer</dt><dd class="text-right font-medium text-slate-700">{{ $application->reviewer?->name ?? 'Unavailable' }}</dd></div></dl>
                @endif
            </aside>
        </div>
    </div>
@endsection
