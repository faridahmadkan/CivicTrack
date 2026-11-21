@props(['href' => null, 'variant' => 'default', 'icon' => null, 'class' => '', 'attributes' => []])

<?php
$variants = [
    'default' => 'dropdown-item',
    'danger' => 'dropdown-item-danger',
];

$classes = [$variants[$variant] ?? 'dropdown-item'];
if ($class) $classes[] = $class;
?>

@if($href)
    <a href="{{ $href }}" class="{{ implode(' ', $classes) }}" role="menuitem" {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
        @if($icon)
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path></svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="button" class="{{ implode(' ', $classes) }}" role="menuitem" {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
        @if($icon)
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path></svg>
        @endif
        {{ $slot }}
    </button>
@endif