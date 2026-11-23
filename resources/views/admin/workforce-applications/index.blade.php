@extends('layouts.admin')

@section('title', 'Workforce applications')
@section('eyebrow', 'Workforce approvals')

@section('content')
    <x-back-link href="{{ route('admin.home') }}" class="mb-6">Administration home</x-back-link>
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Identity and access management</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Workforce applications</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Review applicant information and supporting documents. Approval grants one role and an organization membership in a single transaction.</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('admin.workforce-applications.index') }}" class="rounded-lg border border-line bg-white px-3.5 py-2 text-xs font-semibold {{ $selectedType === null ? 'text-forest-900 ring-1 ring-forest-700/20' : 'text-slate-600' }}">All permitted</a>@foreach ($availableTypes as $type)<a href="{{ route('admin.workforce-applications.index', ['type' => $type->value]) }}" class="rounded-lg border border-line bg-white px-3.5 py-2 text-xs font-semibold {{ $selectedType === $type->value ? 'text-forest-900 ring-1 ring-forest-700/20' : 'text-slate-600' }}">{{ ucfirst($type->value) }}</a>@endforeach</div>
    </div>

    <div class="mt-7 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
        <div class="border-b border-line px-5 py-4 sm:px-6"><p class="text-sm font-semibold text-slate-900">Application queue</p><p class="mt-1 text-xs text-slate-500">{{ $applications->total() }} matching records · newest submission first</p></div>
        @if ($applications->isEmpty())
            <div class="px-6 py-14 text-center"><p class="text-sm font-semibold text-slate-900">No applications in this queue</p><p class="mt-1 text-sm text-slate-500">New applications will appear here after submission.</p></div>
        @else
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-line text-left"><thead class="bg-sand-50 text-[0.68rem] font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 sm:px-6">Applicant</th><th class="px-5 py-3">Role and unit</th><th class="px-5 py-3">Submitted</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 sm:px-6"><span class="sr-only">Open</span></th></tr></thead><tbody class="divide-y divide-line">@foreach ($applications as $application)<tr class="transition hover:bg-sand-50/70"><td class="whitespace-nowrap px-5 py-4 sm:px-6"><p class="text-sm font-semibold text-slate-900">{{ $application->user->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $application->application_number }}</p></td><td class="px-5 py-4"><p class="text-sm font-medium text-slate-800">{{ ucfirst($application->application_type->value) }} · {{ $application->position_title }}</p><p class="mt-1 text-xs text-slate-500">{{ $application->requestedUnit?->name ?? 'Unit no longer available' }}</p></td><td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $application->submitted_at->format('M j, Y') }}</td><td class="whitespace-nowrap px-5 py-4"><span class="rounded-full bg-sand-100 px-2.5 py-1 text-[0.68rem] font-semibold text-slate-700">{{ $application->status->label() }}</span></td><td class="whitespace-nowrap px-5 py-4 text-right sm:px-6"><a href="{{ route('admin.workforce-applications.show', $application) }}" class="text-xs font-semibold text-forest-800 hover:text-forest-950">Review →</a></td></tr>@endforeach</tbody></table></div>
            <div class="border-t border-line px-5 py-4 sm:px-6">{{ $applications->links() }}</div>
        @endif
    </div>
@endsection
