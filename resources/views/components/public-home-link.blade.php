@props(['label' => 'Public home'])

<a href="{{ route('public.home') }}" {{ $attributes->class(['group inline-flex min-h-10 items-center gap-2 rounded-xl border border-line bg-white px-3 py-2 text-sm font-semibold text-forest-900 shadow-sm transition hover:border-forest-200 hover:bg-forest-50 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700']) }} aria-label="Go to the CivicTrack public homepage">
    <svg viewBox="0 0 20 20" class="size-4 transition-transform duration-200 group-hover:-translate-y-0.5" fill="none" aria-hidden="true">
        <path d="m3 9 7-5.5L17 9v7.5h-5v-5H8v5H3V9Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <span>{{ $label }}</span>
</a>
