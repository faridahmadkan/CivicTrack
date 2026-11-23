<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <div class="grid gap-5 sm:grid-cols-2">
        <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">Service category
            <select name="service_category_id" required class="mt-2 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal text-slate-800">
                <option value="">Select the service that best fits</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('service_category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('service_category_id')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        </label>
        <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">Short title
            <input name="title" value="{{ old('title') }}" maxlength="160" required class="mt-2 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal" placeholder="For example, damaged sidewalk on Oak Street">
            @error('title')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        </label>
        <label class="block text-sm font-semibold text-slate-800 sm:col-span-2">What is happening?
            <textarea name="description" rows="5" maxlength="5000" required class="mt-2 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal leading-6" placeholder="Describe the issue, when you noticed it, and any detail that could help the service team.">{{ old('description') }}</textarea>
            @error('description')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        </label>
    </div>

    <section class="rounded-2xl border border-line bg-sand-50 p-4 sm:p-5" aria-labelledby="report-location-heading">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 id="report-location-heading" class="text-sm font-semibold text-slate-900">Pin the location</h2>
                <p class="mt-1 max-w-xl text-xs leading-5 text-slate-600">Click the map or use your device location. The precise pin is only available to authorized service staff.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" id="report-map-locate" class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-forest-900 transition hover:bg-forest-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Use my location</button>
                <span class="text-xs text-slate-600" data-location-status role="status" aria-live="polite"></span>
            </div>
        </div>
        <div class="mt-4" data-map-search data-search-url="{{ route('public.places.search') }}" data-search-configured="{{ config('civictrack.map.place_search.enabled') && config('civictrack.map.place_search.configured') ? 'true' : 'false' }}" data-report-location-search>
            <div class="flex flex-col gap-2 sm:flex-row" role="search" data-search-controls>
                <label for="report-place-search" class="sr-only">Search for a place</label>
                <input id="report-place-search" data-place-query type="search" minlength="2" maxlength="120" autocomplete="off" class="min-w-0 flex-1 rounded-lg border border-line bg-white px-3 py-2.5 text-sm focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100" placeholder="Search a street, landmark, or place name">
                <button type="button" data-search-submit class="rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-semibold text-forest-900 transition hover:bg-forest-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Search place</button>
            </div>
            <p class="mt-1.5 text-xs leading-5 text-slate-500">Search terms are sent to {{ config('civictrack.map.place_search.name') }}. Your report pin coordinates are not sent. Confirm a result before it changes the report location.</p>
            <p class="text-xs text-slate-500">Provider attribution: {!! config('civictrack.map.place_search.attribution') !!}</p>
            <p class="mt-2 text-sm text-slate-700" data-search-status role="status" aria-live="polite"></p>
            <ul class="mt-2 hidden space-y-2" data-search-results aria-label="Place search results"></ul>
            <div class="mt-3 hidden rounded-lg border border-forest-200 bg-white p-3" data-search-confirmation>
                <p class="text-sm font-semibold text-slate-800" data-search-selection></p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" data-confirm-search class="rounded-lg bg-forest-900 px-3 py-2 text-xs font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Use this location</button>
                    <button type="button" data-cancel-search class="rounded-lg border border-line px-3 py-2 text-xs font-semibold text-slate-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Cancel</button>
                </div>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2" data-map-layer-controls role="group" aria-label="Map imagery">
            <button type="button" data-map-layer="street" aria-pressed="true" class="rounded-lg border border-forest-800 bg-forest-900 px-3 py-2 text-xs font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Street map</button>
            <button type="button" data-map-layer="satellite" aria-pressed="false" @disabled(blank(config('civictrack.map.satellite_tile_url'))) class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-slate-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 disabled:cursor-not-allowed disabled:opacity-50">Satellite</button>
            <span class="text-xs text-slate-600" data-map-tile-status role="status" aria-live="polite">{{ blank(config('civictrack.map.satellite_tile_url')) ? 'Satellite imagery is not configured. Street map is active.' : 'Street map is active.' }}</span>
        </div>
        <div class="mt-4 overflow-hidden rounded-xl border border-line bg-white">
            <div id="report-location-map" class="report-map" data-report-map data-map-center='@json($mapCenter)' data-map-zoom="2" data-street-tile-url="{{ config('civictrack.map.street_tile_url') }}" data-street-attribution="{{ config('civictrack.map.street_attribution') }}" data-satellite-tile-url="{{ config('civictrack.map.satellite_tile_url') }}" data-satellite-attribution="{{ config('civictrack.map.satellite_attribution') }}" data-latitude-input="report-latitude" data-longitude-input="report-longitude" data-location-output="selected-location" data-address-input="report-address" data-locate-button="report-map-locate" role="application" aria-label="Interactive map. Click to select a report location."></div>
        </div>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
            <span>Selected coordinates</span>
            <output id="selected-location" class="font-mono text-slate-800">Choose a point on the map</output>
        </div>
        <input type="hidden" id="report-latitude" name="latitude" value="{{ old('latitude') }}">
        <input type="hidden" id="report-longitude" name="longitude" value="{{ old('longitude') }}">
        @error('latitude')<span class="mt-2 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        @error('longitude')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        <label class="mt-4 block text-sm font-medium text-slate-700">Nearby address or landmark <span class="font-normal text-slate-500">(optional)</span>
            <input id="report-address" name="address" value="{{ old('address') }}" maxlength="255" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm" placeholder="Street, intersection, or nearby landmark">
        </label>
    </section>

    <label class="block text-sm font-semibold text-slate-800">Photos or supporting documents <span class="font-normal text-slate-500">(optional, up to 5 files)</span>
        <input type="file" name="documents[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf" class="mt-2 block w-full rounded-xl border border-line bg-white px-3 py-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-forest-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-forest-900">
        <span class="mt-1.5 block text-xs leading-5 text-slate-500">JPEG, PNG, WebP, or PDF. Each file must be 10 MB or smaller. Uploaded files stay private to the report.</span>
        @error('documents')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        @error('documents.*')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
    </label>

    @guest
        <label class="block text-sm font-semibold text-slate-800">Email for contact <span class="font-normal text-slate-500">(optional)</span>
            <input type="email" name="guest_email" value="{{ old('guest_email') }}" maxlength="254" autocomplete="email" class="mt-2 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal" placeholder="you@example.org">
            <span class="mt-1.5 block text-xs leading-5 text-slate-500">If supplied, it is encrypted at rest. You will still need the report reference and access code to view the report.</span>
            @error('guest_email')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        </label>
    @endguest

    <div class="flex flex-col gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-xl text-xs leading-5 text-slate-500">Submitting a report shares its details and precise location with the responsible public service team.</p>
        <button type="submit" @disabled($categories->isEmpty()) class="shrink-0 rounded-lg bg-forest-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-800 disabled:cursor-not-allowed disabled:opacity-50">Submit report</button>
    </div>
</form>
