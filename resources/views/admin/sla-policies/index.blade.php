@extends('layouts.admin')

@section('title', 'Service level policies')
@section('eyebrow', 'Response targets and escalation')

@section('content')
    <a href="{{ route('admin.home') }}" class="mb-6 inline-flex rounded-lg text-sm font-semibold text-forest-800 hover:text-forest-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">← Administration home</a>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Accountability controls</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Service level policies</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Set first-response and resolution targets for reports. The most specific active policy applies when a report is submitted; each report keeps a snapshot of that policy and its deadlines.</p>
        </div>
        <span class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $policies->count() }} policies</span>
    </div>

    <section class="mt-7 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6" aria-labelledby="new-policy-heading">
        <div class="border-b border-line pb-4">
            <h2 id="new-policy-heading" class="text-base font-semibold text-forest-950">Create a policy</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">Leave a scope blank to match any value. The system prefers category scope, then unit, then priority.</p>
        </div>
        <form method="POST" action="{{ route('admin.sla-policies.store') }}" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @csrf
            <label class="block text-sm font-medium text-slate-700 xl:col-span-2">Policy name
                <input name="name" value="{{ old('name') }}" maxlength="120" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm" placeholder="Standard street maintenance">
            </label>
            <label class="block text-sm font-medium text-slate-700">Category scope
                <select name="service_category_id" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('service_category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm font-medium text-slate-700">Service unit scope
                <select name="organization_unit_id" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                    <option value="">All units</option>
                    @foreach ($organizationUnits as $unit)
                        <option value="{{ $unit->id }}" @selected(old('organization_unit_id') == $unit->id)>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm font-medium text-slate-700">Priority scope
                <select name="priority" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                    <option value="">All priorities</option>
                    @foreach (\App\Enums\CivicReportPriority::cases() as $priority)
                        <option value="{{ $priority->value }}" @selected(old('priority') === $priority->value)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm font-medium text-slate-700">First response (minutes)
                <input type="number" name="first_response_minutes" value="{{ old('first_response_minutes', 240) }}" min="1" max="525600" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
            </label>
            <label class="block text-sm font-medium text-slate-700">Resolution (minutes)
                <input type="number" name="resolution_minutes" value="{{ old('resolution_minutes', 4320) }}" min="1" max="525600" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
            </label>
            <label class="block text-sm font-medium text-slate-700">Repeat escalation (minutes)
                <input type="number" name="escalation_repeat_minutes" value="{{ old('escalation_repeat_minutes', 60) }}" min="1" max="525600" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
            </label>
            <label class="block text-sm font-medium text-slate-700">Effective from
                <input type="datetime-local" name="effective_at" value="{{ old('effective_at') }}" class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
            </label>
            <label class="block text-sm font-medium text-slate-700">Expires at
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
            </label>
            <label class="block text-sm font-medium text-slate-700">Policy state
                <select name="is_active" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                    <option value="1" @selected(old('is_active', '1') == '1')>Active</option>
                    <option value="0" @selected(old('is_active') == '0')>Inactive</option>
                </select>
            </label>
            <div class="flex items-end xl:col-span-4"><button class="rounded-lg bg-forest-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-800">Create policy</button></div>
        </form>
    </section>

    <section class="mt-8 space-y-4" aria-label="Existing service level policies">
        @forelse ($policies as $policy)
            <form method="POST" action="{{ route('admin.sla-policies.update', $policy) }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                @csrf
                @method('PUT')
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-4">
                    <div>
                        <p class="text-[0.68rem] font-bold uppercase tracking-[0.16em] text-slate-500">Version {{ $policy->version }} · {{ $policy->category?->name ?? 'All categories' }} · {{ $policy->organizationUnit?->name ?? 'All units' }} · {{ $policy->priority ? ucfirst($policy->priority) : 'All priorities' }}</p>
                        <h2 class="mt-1 text-base font-semibold text-forest-950">{{ $policy->name }}</h2>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $policy->is_active ? 'bg-forest-50 text-forest-800' : 'bg-sand-100 text-slate-500' }}">{{ $policy->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <label class="block text-sm font-medium text-slate-700 xl:col-span-2">Policy name
                        <input name="name" value="{{ $policy->name }}" maxlength="120" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Category scope
                        <select name="service_category_id" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($policy->service_category_id == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Service unit scope
                        <select name="organization_unit_id" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                            <option value="">All units</option>
                            @foreach ($organizationUnits as $unit)
                                <option value="{{ $unit->id }}" @selected($policy->organization_unit_id == $unit->id)>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Priority scope
                        <select name="priority" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                            <option value="">All priorities</option>
                            @foreach (\App\Enums\CivicReportPriority::cases() as $priority)
                                <option value="{{ $priority->value }}" @selected($policy->priority === $priority->value)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-slate-700">First response (minutes)
                        <input type="number" name="first_response_minutes" value="{{ $policy->first_response_minutes }}" min="1" max="525600" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Resolution (minutes)
                        <input type="number" name="resolution_minutes" value="{{ $policy->resolution_minutes }}" min="1" max="525600" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Repeat escalation (minutes)
                        <input type="number" name="escalation_repeat_minutes" value="{{ $policy->escalation_repeat_minutes }}" min="1" max="525600" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Effective from
                        <input type="datetime-local" name="effective_at" value="{{ $policy->effective_at?->format('Y-m-d\\TH:i') }}" class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Expires at
                        <input type="datetime-local" name="expires_at" value="{{ $policy->expires_at?->format('Y-m-d\\TH:i') }}" class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Policy state
                        <select name="is_active" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                            <option value="1" @selected($policy->is_active)>Active</option>
                            <option value="0" @selected(! $policy->is_active)>Inactive</option>
                        </select>
                    </label>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                    <p class="text-xs text-slate-500">{{ $policy->instances_count ?? $policy->instances()->count() }} report(s) retain this policy snapshot.</p>
                    <button class="rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-semibold text-forest-900 transition hover:bg-forest-50">Save as new version</button>
                </div>
            </form>
        @empty
            <div class="rounded-2xl border border-dashed border-line bg-white p-8 text-center">
                <h2 class="text-base font-semibold text-slate-800">No service targets configured</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Reports will not receive SLA deadlines until an administrator creates an active matching policy.</p>
            </div>
        @endforelse
    </section>
@endsection
