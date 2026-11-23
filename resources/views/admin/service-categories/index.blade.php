@extends('layouts.admin')

@section('title', 'Service catalog')
@section('eyebrow', 'Service catalog and report routing')

@section('content')
    <x-back-link href="{{ route('admin.home') }}" class="mb-6">Administration home</x-back-link>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Public service catalog</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Report categories</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Categories define what residents can report and the service unit that receives each report. Reports without a configured destination remain visible in the unassigned triage queue.</p>
        </div>
        <span class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $categories->total() }} categories</span>
    </div>

    <div class="mt-7 grid gap-6 xl:grid-cols-[minmax(18rem,0.8fr)_minmax(0,1.2fr)]">
        <section class="h-fit rounded-2xl border border-line bg-white p-5 shadow-sm" aria-labelledby="new-category-heading">
            <div class="border-b border-line pb-4">
                <h2 id="new-category-heading" class="text-base font-semibold text-forest-950">Add a category</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">New categories are immediately available when active and public.</p>
            </div>
            <form method="POST" action="{{ route('admin.service-categories.store') }}" class="mt-4 space-y-4">
                @csrf
                <label class="block text-sm font-medium text-slate-700">Category code
                    <input name="code" value="{{ old('code') }}" maxlength="32" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm uppercase" placeholder="ROADS">
                </label>
                <label class="block text-sm font-medium text-slate-700">Name
                    <input name="name" value="{{ old('name') }}" maxlength="120" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm" placeholder="Roads and sidewalks">
                </label>
                <label class="block text-sm font-medium text-slate-700">Description
                    <textarea name="description" rows="3" maxlength="2000" class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm" placeholder="Help residents choose the right service.">{{ old('description') }}</textarea>
                </label>
                <label class="block text-sm font-medium text-slate-700">Default service unit
                    <select name="default_unit_id" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                        <option value="">Unassigned triage queue</option>
                        @foreach ($organizationUnits as $unit)
                            <option value="{{ $unit->id }}" @selected(old('default_unit_id') == $unit->id)>{{ $unit->name }} · {{ $unit->code }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-700">Location pin
                        <select name="requires_location" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                            <option value="1" @selected(old('requires_location', '1') == '1')>Required</option>
                            <option value="0" @selected(old('requires_location') == '0')>Optional</option>
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Public reporting
                        <select name="is_public" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                            <option value="1" @selected(old('is_public', '1') == '1')>Available</option>
                            <option value="0" @selected(old('is_public') == '0')>Hidden</option>
                        </select>
                    </label>
                </div>
                <input type="hidden" name="is_active" value="1">
                <input type="hidden" name="sort_order" value="0">
                <button class="w-full rounded-lg bg-forest-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-forest-800">Create category</button>
            </form>
        </section>

        <section class="space-y-4" aria-label="Existing service categories">
            <form method="GET" action="{{ route('admin.service-categories.index') }}" class="grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-2 xl:grid-cols-[minmax(12rem,1fr)_11rem_11rem_auto_auto] xl:items-end">
                <label class="text-xs font-semibold text-slate-600">Search categories
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800" placeholder="Name, code, description, or unit">
                </label>
                <label class="text-xs font-semibold text-slate-600">Category status
                    <select name="status" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All statuses</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option></select>
                </label>
                <label class="text-xs font-semibold text-slate-600">Public visibility
                    <select name="visibility" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All visibility</option><option value="public" @selected(($filters['visibility'] ?? '') === 'public')>Public</option><option value="hidden" @selected(($filters['visibility'] ?? '') === 'hidden')>Hidden</option></select>
                </label>
                <button class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Search categories</button>
                <a href="{{ route('admin.service-categories.index') }}" class="rounded-lg border border-line bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-sand-50">Clear</a>
            </form>
            @forelse ($categories as $category)
                <form method="POST" action="{{ route('admin.service-categories.update', $category) }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    @csrf
                    @method('PUT')
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-4">
                        <div>
                            <p class="text-[0.68rem] font-bold uppercase tracking-[0.16em] text-slate-500">{{ $category->code }}</p>
                            <h2 class="mt-1 text-base font-semibold text-forest-950">{{ $category->name }}</h2>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $category->is_active ? 'bg-forest-50 text-forest-800' : 'bg-sand-100 text-slate-500' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-medium text-slate-700">Code
                            <input name="code" value="{{ $category->code }}" maxlength="32" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm uppercase">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Name
                            <input name="name" value="{{ $category->name }}" maxlength="120" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                        </label>
                        <label class="block text-sm font-medium text-slate-700 sm:col-span-2">Description
                            <textarea name="description" rows="2" maxlength="2000" class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">{{ $category->description }}</textarea>
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Default service unit
                            <select name="default_unit_id" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                                <option value="">Unassigned triage queue</option>
                                @foreach ($organizationUnits as $unit)
                                    <option value="{{ $unit->id }}" @selected($category->default_unit_id == $unit->id)>{{ $unit->name }} · {{ $unit->code }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Category state
                            <select name="is_active" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                                <option value="1" @selected($category->is_active)>Active</option>
                                <option value="0" @selected(! $category->is_active)>Inactive</option>
                            </select>
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Location pin
                            <select name="requires_location" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                                <option value="1" @selected($category->requires_location)>Required</option>
                                <option value="0" @selected(! $category->requires_location)>Optional</option>
                            </select>
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Public reporting
                            <select name="is_public" required class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm">
                                <option value="1" @selected($category->is_public)>Available</option>
                                <option value="0" @selected(! $category->is_public)>Hidden</option>
                            </select>
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Sort order
                            <input type="number" name="sort_order" min="0" max="65535" value="{{ $category->sort_order }}" required class="mt-1.5 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                        </label>
                    </div>
                    <div class="mt-4 flex justify-end"><button class="rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-semibold text-forest-900 transition hover:bg-forest-50">Save changes</button></div>
                </form>
            @empty
                @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || filled($filters['visibility'] ?? null))
                    <x-empty-state title="No categories match these filters" description="Try another search term, status, or visibility, or clear the filters to see the full catalog." :href="route('admin.service-categories.index')" action="Clear filters" />
                @else
                    <x-empty-state title="No categories configured" description="Add a service category using the form at the left, or run the initial database seeder to install the standard civic catalog." />
                @endif
            @endforelse
            @if ($categories->hasPages())
                <div>{{ $categories->links() }}</div>
            @endif
        </section>
    </div>
@endsection
