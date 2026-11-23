@extends('layouts.admin')

@section('title', 'Organization units')
@section('eyebrow', 'Organization structure')

@section('content')
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Operating structure</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Organization units</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Create departments and nested teams that scope staff membership and approved workforce applications. Units are archived by deactivation so historical decisions remain intact.</p>
    </div>

    <div class="mt-7 grid gap-6 xl:grid-cols-[22rem_1fr]">
        <section class="h-fit rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-base font-semibold text-slate-900">Add organization unit</h2>
            <form method="POST" action="{{ route('admin.organization-units.store') }}" class="mt-5 space-y-4">
                @csrf
                <div><label for="new-code" class="mb-1.5 block text-xs font-semibold text-slate-700">Unit code</label><input id="new-code" name="code" value="{{ old('code') }}" required maxlength="32" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm uppercase focus:border-forest-700 focus:ring-4 focus:ring-forest-100" placeholder="PUBLIC-WORKS">@error('code')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="new-name" class="mb-1.5 block text-xs font-semibold text-slate-700">Unit name</label><input id="new-name" name="name" value="{{ old('name') }}" required maxlength="160" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100" placeholder="Public Works Department">@error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="new-type" class="mb-1.5 block text-xs font-semibold text-slate-700">Unit type</label><select id="new-type" name="unit_type" required class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100"><option value="">Choose a type</option>@foreach ($unitTypes as $unitType)<option value="{{ $unitType->value }}" @selected(old('unit_type') === $unitType->value)>{{ ucfirst($unitType->value) }}</option>@endforeach</select>@error('unit_type')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="new-parent" class="mb-1.5 block text-xs font-semibold text-slate-700">Parent unit <span class="font-normal text-slate-500">(optional)</span></label><select id="new-parent" name="parent_id" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100"><option value="">No parent</option>@foreach ($activeParents as $parent)<option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>{{ $parent->name }} · {{ ucfirst($parent->unit_type->value) }}</option>@endforeach</select>@error('parent_id')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="new-description" class="mb-1.5 block text-xs font-semibold text-slate-700">Description <span class="font-normal text-slate-500">(optional)</span></label><textarea id="new-description" name="description" rows="3" maxlength="3000" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">{{ old('description') }}</textarea></div>
                <button type="submit" class="w-full rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-800">Create unit</button>
            </form>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between gap-4"><h2 class="text-base font-semibold text-slate-900">Organization directory</h2><span class="text-xs text-slate-500">{{ $organizationUnits->count() }} {{ \Illuminate\Support\Str::plural('unit', $organizationUnits->count()) }}</span></div>
            @forelse ($organizationUnits as $organizationUnit)
                <article class="mb-3 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-lg bg-forest-50 text-xs font-bold text-forest-800">{{ strtoupper(substr($organizationUnit->unit_type->value, 0, 2)) }}</span><div><h3 class="text-sm font-semibold text-slate-900">{{ $organizationUnit->name }}</h3><p class="mt-0.5 text-xs text-slate-500">{{ $organizationUnit->code }} · {{ $organizationUnit->parent?->name ?? 'Top-level unit' }}</p></div></div>
                        @if ($organizationUnit->is_active)<span class="rounded-full bg-forest-50 px-2.5 py-1 text-[0.68rem] font-semibold text-forest-800">Active</span>@else<span class="rounded-full bg-sand-100 px-2.5 py-1 text-[0.68rem] font-semibold text-slate-600">Inactive</span>@endif
                    </div>
                    <form method="POST" action="{{ route('admin.organization-units.update', $organizationUnit) }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @csrf
                        @method('PUT')
                        <div><label for="code-{{ $organizationUnit->id }}" class="mb-1 block text-[0.68rem] font-semibold text-slate-600">Code</label><input id="code-{{ $organizationUnit->id }}" name="code" value="{{ $organizationUnit->code }}" required maxlength="32" class="w-full rounded-lg border border-line px-3 py-2 text-sm uppercase"></div>
                        <div><label for="name-{{ $organizationUnit->id }}" class="mb-1 block text-[0.68rem] font-semibold text-slate-600">Name</label><input id="name-{{ $organizationUnit->id }}" name="name" value="{{ $organizationUnit->name }}" required maxlength="160" class="w-full rounded-lg border border-line px-3 py-2 text-sm"></div>
                        <div><label for="type-{{ $organizationUnit->id }}" class="mb-1 block text-[0.68rem] font-semibold text-slate-600">Unit type</label><select id="type-{{ $organizationUnit->id }}" name="unit_type" required class="w-full rounded-lg border border-line bg-white px-3 py-2 text-sm">@foreach ($unitTypes as $unitType)<option value="{{ $unitType->value }}" @selected($organizationUnit->unit_type === $unitType)>{{ ucfirst($unitType->value) }}</option>@endforeach</select></div>
                        <div><label for="parent-{{ $organizationUnit->id }}" class="mb-1 block text-[0.68rem] font-semibold text-slate-600">Parent unit</label><select id="parent-{{ $organizationUnit->id }}" name="parent_id" class="w-full rounded-lg border border-line bg-white px-3 py-2 text-sm"><option value="">No parent</option>@foreach ($activeParents->where('id', '!=', $organizationUnit->id) as $parent)<option value="{{ $parent->id }}" @selected($organizationUnit->parent_id === $parent->id)>{{ $parent->name }} · {{ ucfirst($parent->unit_type->value) }}</option>@endforeach</select></div>
                        <div class="md:col-span-2"><label for="description-{{ $organizationUnit->id }}" class="mb-1 block text-[0.68rem] font-semibold text-slate-600">Description</label><input id="description-{{ $organizationUnit->id }}" name="description" value="{{ $organizationUnit->description }}" maxlength="3000" class="w-full rounded-lg border border-line px-3 py-2 text-sm"></div>
                        <div class="flex items-end justify-between gap-3">
                            <label for="active-{{ $organizationUnit->id }}" class="inline-flex items-center gap-2 text-xs font-medium text-slate-600"><input type="hidden" name="is_active" value="0"><input id="active-{{ $organizationUnit->id }}" type="checkbox" name="is_active" value="1" @checked($organizationUnit->is_active) class="size-4 rounded border-slate-300 text-forest-800">Active</label>
                            <button type="submit" class="rounded-lg border border-forest-900/15 bg-white px-3.5 py-2 text-xs font-semibold text-forest-900 transition hover:bg-forest-50">Save changes</button>
                        </div>
                    </form>
                    @if ($organizationUnit->description)<p class="mt-3 border-t border-line pt-3 text-xs leading-5 text-slate-500">{{ $organizationUnit->description }}</p>@endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center"><p class="text-sm font-semibold text-slate-800">No organization units yet</p><p class="mt-1 text-sm text-slate-500">Add a department to start structuring the service organization.</p></div>
            @endforelse
        </section>
    </div>
@endsection
