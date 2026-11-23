@props(['mapCenter', 'idPrefix' => 'citizen-home'])

<section class="rounded-2xl border border-line bg-sand-50 p-4 sm:col-span-2 sm:p-5" aria-labelledby="citizen-profile-heading">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-forest-700">Resident profile</p>
        <h2 id="citizen-profile-heading" class="mt-1 text-base font-semibold text-forest-950">Help us understand your service area</h2>
        <p class="mt-1 text-xs leading-5 text-slate-600">Your age and neighborhood help staff understand who and where services reach. The map pin is optional, stored at neighborhood-level precision, and never shown on the public map.</p>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <label for="{{ $idPrefix }}-age" class="block text-sm font-semibold text-slate-800">Age
            <input id="{{ $idPrefix }}-age" name="age" type="number" min="13" max="120" step="1" value="{{ old('age') }}" required inputmode="numeric" autocomplete="off" class="mt-1.5 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal shadow-sm transition placeholder:text-slate-400 focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100" placeholder="Your age">
            @error('age')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        </label>
        <label for="{{ $idPrefix }}-phone" class="block text-sm font-semibold text-slate-800">Phone <span class="font-normal text-slate-500">(optional)</span>
            <input id="{{ $idPrefix }}-phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" class="mt-1.5 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal shadow-sm transition placeholder:text-slate-400 focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100" placeholder="+93 700 000 000">
            @error('phone')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        </label>
        <label for="{{ $idPrefix }}-locality" class="block text-sm font-semibold text-slate-800 sm:col-span-2">City, district, or neighborhood
            <input id="{{ $idPrefix }}-locality" name="locality" type="text" value="{{ old('locality') }}" required maxlength="120" autocomplete="address-level2" class="mt-1.5 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal shadow-sm transition placeholder:text-slate-400 focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100" placeholder="For example, Karte Seh, Kabul">
            @error('locality')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        </label>
    </div>

    <div class="mt-5 border-t border-line pt-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h3 class="text-sm font-semibold text-slate-900">Pin your neighborhood <span class="font-normal text-slate-500">(optional)</span></h3><p class="mt-1 text-xs leading-5 text-slate-600">Choose a general area, not your exact home address.</p></div>
            <button type="button" id="{{ $idPrefix }}-locate" class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-3.5 py-2.5 text-xs font-semibold text-forest-900 shadow-sm transition hover:border-forest-200 hover:bg-forest-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">
                <svg viewBox="0 0 20 20" class="size-4" fill="none" aria-hidden="true"><path d="M10 17s5.5-4.1 5.5-10a5.5 5.5 0 1 0-11 0c0 5.9 5.5 10 5.5 10Z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="7" r="1.8" stroke="currentColor" stroke-width="1.5"/></svg>
                Use device location
            </button>
        </div>
        <p class="mt-2 text-xs text-slate-600" data-location-status role="status" aria-live="polite"></p>

        <div class="mt-4" data-map-search data-search-url="{{ route('public.places.search') }}" data-search-configured="{{ config('civictrack.map.place_search.enabled') && config('civictrack.map.place_search.configured') ? 'true' : 'false' }}">
            <div class="flex flex-col gap-2 sm:flex-row" role="search" data-search-controls>
                <label for="{{ $idPrefix }}-place-search" class="sr-only">Search for a neighborhood or place</label>
                <input id="{{ $idPrefix }}-place-search" data-place-query type="search" minlength="2" maxlength="120" autocomplete="off" class="min-w-0 flex-1 rounded-xl border border-line bg-white px-3 py-2.5 text-sm shadow-sm focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100" placeholder="Search for a neighborhood or landmark">
                <button type="button" data-search-submit class="inline-flex items-center justify-center gap-2 rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-semibold text-forest-900 transition hover:border-forest-200 hover:bg-forest-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <svg viewBox="0 0 20 20" class="size-4" fill="none" aria-hidden="true"><circle cx="8.7" cy="8.7" r="5.7" stroke="currentColor" stroke-width="1.5"/><path d="m13 13 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Find place
                </button>
            </div>
            <p class="mt-1.5 text-xs leading-5 text-slate-500">Place searches go to {{ config('civictrack.map.place_search.name') }}. Your profile pin coordinates are not sent with the search.</p>
            <p class="text-xs text-slate-500">Provider attribution: {!! config('civictrack.map.place_search.attribution') !!}</p>
            <p class="mt-2 text-sm text-slate-700" data-search-status role="status" aria-live="polite"></p>
            <ul class="mt-2 hidden space-y-2" data-search-results aria-label="Place search results"></ul>
            <div class="mt-3 hidden rounded-xl border border-forest-200 bg-white p-3 shadow-sm" data-search-confirmation>
                <p class="text-sm font-semibold text-slate-800" data-search-selection></p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" data-confirm-search class="rounded-lg bg-forest-900 px-3 py-2 text-xs font-semibold text-white hover:bg-forest-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Use this neighborhood</button>
                    <button type="button" data-cancel-search class="rounded-lg border border-line px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-sand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Cancel</button>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2" data-map-layer-controls role="group" aria-label="Map imagery">
            <button type="button" data-map-layer="street" aria-pressed="true" class="rounded-lg border border-forest-800 bg-forest-900 px-3 py-2 text-xs font-semibold text-white">Street map</button>
            <button type="button" data-map-layer="satellite" aria-pressed="false" @disabled(blank(config('civictrack.map.satellite_tile_url'))) class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-slate-700 disabled:cursor-not-allowed disabled:opacity-50">Satellite</button>
            <span class="text-xs text-slate-600" data-map-tile-status role="status" aria-live="polite">{{ blank(config('civictrack.map.satellite_tile_url')) ? 'Street map is active.' : 'Street map is active.' }}</span>
        </div>

        <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
            <div id="{{ $idPrefix }}-map" class="report-map" data-report-map data-map-center='@json($mapCenter)' data-map-zoom="11" data-coordinate-precision="3" data-street-tile-url="{{ config('civictrack.map.street_tile_url') }}" data-street-attribution="{{ config('civictrack.map.street_attribution') }}" data-satellite-tile-url="{{ config('civictrack.map.satellite_tile_url') }}" data-satellite-attribution="{{ config('civictrack.map.satellite_attribution') }}" data-latitude-input="{{ $idPrefix }}-latitude" data-longitude-input="{{ $idPrefix }}-longitude" data-location-output="{{ $idPrefix }}-selected-location" data-locate-button="{{ $idPrefix }}-locate" role="application" aria-label="Interactive neighborhood map. Click to choose a general area."></div>
        </div>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600"><span>Selected neighborhood point</span><output id="{{ $idPrefix }}-selected-location" class="font-mono text-slate-800">No map pin selected</output></div>
        <input type="hidden" id="{{ $idPrefix }}-latitude" name="home_latitude" value="{{ old('home_latitude') }}">
        <input type="hidden" id="{{ $idPrefix }}-longitude" name="home_longitude" value="{{ old('home_longitude') }}">
        @error('home_latitude')<span class="mt-2 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        @error('home_longitude')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
    </div>
</section>
