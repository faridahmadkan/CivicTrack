@props(['category'])

@php($icon = strtoupper((string) $category->code))

<span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-forest-50 text-forest-800" aria-hidden="true">
    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
        @switch($icon)
            @case('ROAD')
                <path d="m8 3-3 18m11-18 3 18M12 5v3m0 4v3m0 4v2" />
                @break
            @case('LIGHT')
                <path d="M9 18h6m-5 3h4m-2-6a6 6 0 1 0-4.2-1.7c.8.8 1.2 1.5 1.2 2.7h6c0-1.2.4-1.9 1.2-2.7A6 6 0 0 0 12 3" />
                @break
            @case('WASTE')
                <path d="M4 7h16m-14 0 1 14h10l1-14M9 7V4h6v3m-5 4v6m4-6v6" />
                @break
            @case('WATER')
                <path d="M12 3s6 6.3 6 11a6 6 0 0 1-12 0c0-4.7 6-11 6-11Z" />
                <path d="M9 15a3 3 0 0 0 3 3" />
                @break
            @case('PARK')
                <path d="M19.5 4.5c-8 0-13 3-13 9a6 6 0 0 0 6 6c6 0 9-5 9-13V4.5h-2Z" />
                <path d="m5 21 9-9" />
                @break
            @case('FACILITY')
                <path d="M4 21V5l8-2 8 2v16M8 9h1m6 0h1M8 13h1m6 0h1M10 21v-4h4v4" />
                @break
            @default
                <path d="M12 21s7-5.1 7-12a7 7 0 1 0-14 0c0 6.9 7 12 7 12Z" />
                <circle cx="12" cy="9" r="2.2" />
        @endswitch
    </svg>
</span>
