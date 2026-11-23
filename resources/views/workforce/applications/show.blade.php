@extends('layouts.citizen')

@section('title', 'Application '.$application->application_number)

@section('content')
    <div class="max-w-4xl">
    <x-back-link href="{{ route('workforce.applications.index') }}">Workforce applications</x-back-link>
        <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">{{ ucfirst($application->application_type->value) }} application</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">{{ $application->position_title }}</h1><p class="mt-2 text-sm text-slate-600">{{ $application->application_number }} · Submitted {{ $application->submitted_at->format('M j, Y') }}</p></div>
            <span class="w-fit rounded-full bg-sand-100 px-3 py-1.5 text-xs font-semibold text-slate-700">{{ $application->status->label() }}</span>
        </div>

        <div class="mt-7 grid gap-5 lg:grid-cols-[1fr_18rem]">
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-sm font-semibold text-slate-900">Application details</h2>
                <dl class="mt-4 space-y-4 text-sm"><div><dt class="text-xs font-semibold text-slate-500">Requested organization unit</dt><dd class="mt-1 text-slate-800">{{ $application->requestedUnit?->name ?? 'Unit no longer available' }}</dd></div><div><dt class="text-xs font-semibold text-slate-500">Relevant experience</dt><dd class="mt-1 whitespace-pre-line leading-6 text-slate-700">{{ $application->experience_summary ?: 'Not provided' }}</dd></div><div><dt class="text-xs font-semibold text-slate-500">Qualifications and skills</dt><dd class="mt-1 whitespace-pre-line leading-6 text-slate-700">{{ $application->qualification_summary ?: 'Not provided' }}</dd></div>@if ($application->additional_information)<div><dt class="text-xs font-semibold text-slate-500">Additional information</dt><dd class="mt-1 whitespace-pre-line leading-6 text-slate-700">{{ $application->additional_information }}</dd></div>@endif</dl>
                @if ($application->requested_information)<div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4"><p class="text-sm font-semibold text-amber-950">More information is needed</p><p class="mt-1 whitespace-pre-line text-sm leading-6 text-amber-900">{{ $application->requested_information }}</p></div>@endif
            </section>

            <aside class="space-y-5">
                <section class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900">Supporting documents</h2>
                    @if ($application->documents->isEmpty())<p class="mt-2 text-sm leading-6 text-slate-500">No documents were attached.</p>@else<ul class="mt-3 space-y-2">@foreach ($application->documents as $document)<li><a href="{{ route('workforce.application-documents.download', $document) }}" class="flex items-center gap-2 text-sm font-medium text-forest-800 hover:text-forest-950"><svg viewBox="0 0 20 20" class="size-4 shrink-0" fill="none" aria-hidden="true"><path d="M6 2.5h5l4 4V17H5V2.5h1Zm4 0v5h5M7.5 12h5m-5 2.5h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg><span class="truncate">{{ $document->original_name }}</span></a></li>@endforeach</ul>@endif
                </section>
                @if ($application->status === App\Enums\WorkforceApplicationStatus::InformationRequested)
                    <section class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-900">Send additional information</h2>
                        <form method="POST" action="{{ route('workforce.applications.information', $application) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                            @csrf @method('PUT')
                            <label for="additional_information" class="sr-only">Additional information</label><textarea id="additional_information" name="additional_information" rows="5" required maxlength="4000" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100" placeholder="Respond to the reviewer’s request">{{ old('additional_information') }}</textarea>
                            <input name="documents[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-600">
                            <button type="submit" class="w-full rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-800">Send for review</button>
                        </form>
                    </section>
                @endif
            </aside>
        </div>

        <section class="mt-7 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-sm font-semibold text-slate-900">Review history</h2>
            <ol class="mt-5 space-y-0">
                @foreach ($application->events as $event)
                    <li class="relative flex gap-4 pb-5 last:pb-0">
                        @if (! $loop->last)<span class="absolute left-[0.45rem] top-3.5 h-full w-px bg-line" aria-hidden="true"></span>@endif
                        <span class="relative mt-1 size-2.5 shrink-0 rounded-full bg-forest-600 ring-4 ring-forest-50"></span>
                        <div class="min-w-0"><p class="text-sm font-semibold text-slate-800">{{ $event->to_status === App\Enums\WorkforceApplicationStatus::Submitted->value ? ($event->event_type === 'information_provided' ? 'Additional information submitted' : 'Application submitted') : App\Enums\WorkforceApplicationStatus::from($event->to_status)->label() }}</p><p class="mt-1 text-xs text-slate-500">{{ $event->occurred_at->format('M j, Y · H:i') }}</p>@if ($event->reason)<p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $event->reason }}</p>@endif</div>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
@endsection
