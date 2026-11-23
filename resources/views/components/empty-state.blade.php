@props(['title', 'description', 'href' => null, 'action' => null])

<div {{ $attributes->class(['rounded-2xl border border-dashed border-line bg-white p-8 text-center sm:p-12']) }}>
    <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-600">{{ $description }}</p>
    @if ($href && $action)
        <a href="{{ $href }}" class="button-primary mt-5">{{ $action }}</a>
    @endif
</div>
