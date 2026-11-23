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
            <button type="button" id="report-map-locate" class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-forest-900 transition hover:bg-forest-50">Use my location</button>
        </div>
        <div class="mt-4 overflow-hidden rounded-xl border border-line bg-white">
            <div id="report-location-map" class="report-map" data-report-map data-map-center='@json($mapCenter)' data-map-zoom="2" data-tile-url="{{ config('civictrack.map.tile_url') }}" data-attribution="{{ config('civictrack.map.attribution') }}" data-latitude-input="report-latitude" data-longitude-input="report-longitude" data-location-output="selected-location" data-locate-button="report-map-locate" role="application" aria-label="Interactive map. Click to select a report location."></div>
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
            <input name="address" value="{{ old('address') }}" maxlength="255" class="mt-1.5 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm" placeholder="Street, intersection, or nearby landmark">
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
