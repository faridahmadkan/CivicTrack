@extends('layouts.admin')

@section('title', 'Workforce applications')
@section('eyebrow', 'Workforce approvals')

@section('content')
    @php($hasActiveFilters = filled($filters['q'] ?? null) || filled($filters['type'] ?? null) || filled($filters['status'] ?? null))
    <x-back-link href="{{ route('admin.home') }}" class="mb-6">Administration home</x-back-link>
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Identity and access management</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Workforce applications</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Review applicant information and supporting documents. Approval grants one role and an organization membership in a single transaction.</p></div>
    </div>

    <form method="GET" action="{{ route('admin.workforce-applications.index') }}" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-2 xl:grid-cols-[minmax(14rem,1fr)_12rem_14rem_auto_auto] xl:items-end">
        <label class="text-xs font-semibold text-slate-600">Search applications
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800" placeholder="Applicant, email, or application number">
        </label>
        <label class="text-xs font-semibold text-slate-600">Application type
            <select name="type" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All permitted types</option>@foreach ($availableTypes as $type)<option value="{{ $type->value }}" @selected($selectedType === $type->value)>{{ ucfirst($type->value) }}</option>@endforeach</select>
        </label>
        <label class="text-xs font-semibold text-slate-600">Status
            <select name="status" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All statuses</option>@foreach (App\Enums\WorkforceApplicationStatus::cases() as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
        </label>
        <button class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Search queue</button>
        <a href="{{ route('admin.workforce-applications.index') }}" class="rounded-lg border border-line bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-sand-50">Clear</a>
    </form>

    <div class="mt-7 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
        <div class="border-b border-line px-5 py-4 sm:px-6"><p class="text-sm font-semibold text-slate-900">Application queue</p><p class="mt-1 text-xs text-slate-500">{{ $applications->total() }} matching records · newest submission first</p></div>
        @if ($applications->isEmpty() && $hasActiveFilters)
            <x-empty-state title="No applications match these filters" description="Try a different search term, application type, or status, or clear the filters to return to the queue." :href="route('admin.workforce-applications.index')" action="Clear filters" />
        @elseif ($applications->isEmpty())
            <x-empty-state title="No applications to review" description="Applications for the staff or worker roles you can review will appear here." :href="route('admin.home')" action="Administration home" />
        @else
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-line text-left"><thead class="bg-sand-50 text-[0.68rem] font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 sm:px-6">Applicant</th><th class="px-5 py-3">Role and unit</th><th class="px-5 py-3">Submitted</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 sm:px-6"><span class="sr-only">Open</span></th></tr></thead><tbody class="divide-y divide-line">@foreach ($applications as $application)<tr class="transition hover:bg-sand-50/70"><td class="whitespace-nowrap px-5 py-4 sm:px-6"><p class="text-sm font-semibold text-slate-900">{{ $application->user->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $application->application_number }}</p></td><td class="px-5 py-4"><p class="text-sm font-medium text-slate-800">{{ ucfirst($application->application_type->value) }} · {{ $application->position_title }}</p><p class="mt-1 text-xs text-slate-500">{{ $application->requestedUnit?->name ?? 'Unit no longer available' }}</p></td><td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $application->submitted_at->format('M j, Y') }}</td><td class="whitespace-nowrap px-5 py-4"><x-status-badge :status="$application->status" /></td><td class="whitespace-nowrap px-5 py-4 text-right sm:px-6"><a href="{{ route('admin.workforce-applications.show', $application) }}" class="text-xs font-semibold text-forest-800 hover:text-forest-950">Review →</a></td></tr>@endforeach</tbody></table></div>
            <div class="border-t border-line px-5 py-4 sm:px-6">{{ $applications->links() }}</div>
        @endif
    </div>
@endsection
