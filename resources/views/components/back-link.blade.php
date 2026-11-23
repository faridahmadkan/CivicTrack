@props(['href'])

<a href="{{ $href }}" {{ $attributes->class(['group inline-flex w-fit items-center gap-2.5 rounded-full border border-line bg-white/90 py-1.5 pl-1.5 pr-4 text-sm font-semibold text-forest-900 shadow-sm transition duration-200 hover:-translate-x-0.5 hover:border-forest-200 hover:bg-forest-50 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700']) }}>
    <span class="flex size-8 items-center justify-center rounded-full bg-forest-50 text-forest-800 transition group-hover:bg-forest-900 group-hover:text-white" aria-hidden="true">
        <svg viewBox="0 0 20 20" class="size-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none">
            <path d="M15.5 10H4.75m0 0 4.5-4.5m-4.5 4.5 4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </span>
    <span>{{ $slot }}</span>
</a>
