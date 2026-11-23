@extends('layouts.public')

@section('title', 'Community service map')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14 lg:px-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Community transparency</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">Published service reports</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Explore reports that service teams approved for public listing. Map markers use generalized locations; private contact details, exact coordinates, and report attachments are not displayed.</p>
            </div>
            <a href="{{ route('public.reports.create') }}" class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Report an issue</a>
        </div>

        <form method="GET" action="{{ route('public.map.index') }}" class="mt-6 flex flex-wrap items-end gap-3 rounded-2xl border border-line bg-white p-4 shadow-sm">
            <label class="text-xs font-semibold text-slate-600">Service category
                <select name="category_id" class="mt-1.5 block min-w-60 rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800">
                    <option value="">All published categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <button class="rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-semibold text-forest-900 hover:bg-forest-50">Filter map</button>
            @if (! empty($filters['category_id']))<a href="{{ route('public.map.index') }}" class="px-2 py-2.5 text-sm font-semibold text-slate-600 hover:text-forest-900">Clear filter</a>@endif
            <span class="ml-auto text-xs text-slate-500">{{ $reports->count() }} published report{{ $reports->count() === 1 ? '' : 's' }}{{ $reports->count() === 500 ? ' · showing the latest 500' : '' }}</span>
        </form>

        <div class="mt-5 overflow-hidden rounded-2xl border border-line bg-white p-2 shadow-sm">
            <div
                id="community-report-map"
                class="report-map min-h-[28rem] rounded-xl"
                data-public-report-map
                data-marker-data="community-report-markers"
                data-map-center="{{ json_encode($mapCenter) }}"
                data-tile-url="{{ $tileUrl }}"
                data-attribution="{{ $mapAttribution }}"
                role="region"
                aria-label="Map of generalized locations for public civic service reports"
            ></div>
        </div>
        <script type="application/json" id="community-report-markers">@json($mapMarkers)</script>
        <p class="mt-2 text-xs leading-5 text-slate-500">Location markers are intentionally generalized for public privacy. Map tiles are provided by the configured mapping service.</p>

        <section class="mt-8 overflow-hidden rounded-2xl border border-line bg-white shadow-sm" aria-labelledby="published-reports-heading">
            <div class="border-b border-line px-5 py-4 sm:px-6">
                <h2 id="published-reports-heading" class="text-base font-semibold text-forest-950">Public report activity</h2>
                <p class="mt-1 text-xs text-slate-500">Only the service category, workflow status, and date are shared here.</p>
            </div>
            @forelse ($reports as $report)
                <article class="flex flex-wrap items-center justify-between gap-3 border-b border-line p-5 last:border-0 sm:px-6">
                    <div><p class="text-sm font-semibold text-slate-900">{{ $report->category->name }}</p><p class="mt-1 text-xs text-slate-500">Published {{ $report->submitted_at->format('M j, Y') }}</p></div>
                    <span class="rounded-full bg-forest-50 px-2.5 py-1 text-xs font-semibold text-forest-900">{{ $report->status->label() }}</span>
                </article>
            @empty
                <div class="p-8 text-center sm:p-12"><h3 class="text-base font-semibold text-slate-900">No reports are published on the map</h3><p class="mt-2 text-sm leading-6 text-slate-600">Reports appear after a service team approves a generalized public listing.</p></div>
            @endforelse
        </section>
    </section>
@endsection
