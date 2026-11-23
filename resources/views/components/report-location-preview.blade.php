@props(['location' => null, 'public' => false])

@php
    $latitude = $public ? $location?->public_latitude : $location?->latitude;
    $longitude = $public ? $location?->public_longitude : $location?->longitude;
    $hasCoordinates = is_numeric($latitude) && is_numeric($longitude);
    $locationLabel = $public
        ? ($hasCoordinates ? 'Approximate area shown on the public map' : 'Approximate area unavailable')
        : ($location?->address ?: ($hasCoordinates ? 'Pinned service location' : 'Location not included'));
@endphp

<div {{ $attributes->class(['location-preview-card shrink-0 sm:w-40']) }}>
    @if ($hasCoordinates)
        <div
            class="location-preview-map h-24 overflow-hidden rounded-lg border border-line bg-sand-100"
            data-location-preview
            data-latitude="{{ $latitude }}"
            data-longitude="{{ $longitude }}"
            data-tile-url="{{ config('civictrack.map.street_tile_url') }}"
            data-tile-attribution="{{ config('civictrack.map.street_attribution') }}"
            data-public-location="{{ $public ? 'true' : 'false' }}"
            role="region"
            aria-label="Map preview for {{ $locationLabel }}"
        ></div>
    @else
        <div class="flex h-24 items-center gap-2 rounded-lg border border-dashed border-line bg-sand-50 px-3 text-xs leading-5 text-slate-600" role="img" aria-label="{{ $locationLabel }}">
            <svg viewBox="0 0 24 24" class="size-5 shrink-0 text-slate-500" fill="none" aria-hidden="true"><path d="M12 21s7-5.1 7-12a7 7 0 1 0-14 0c0 6.9 7 12 7 12Z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="9" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg>
            <span>{{ $locationLabel }}</span>
        </div>
    @endif
    <p class="mt-1.5 truncate text-[0.68rem] text-slate-500" title="{{ $locationLabel }}">{{ $locationLabel }}</p>
</div>
