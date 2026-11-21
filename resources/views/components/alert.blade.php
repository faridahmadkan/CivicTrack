@props(['variant' => 'info', 'dismissible' => true, 'class' => '', 'attributes' => []])

<?php
$variants = [
    'success' => 'alert-success',
    'danger' => 'alert-danger',
    'warning' => 'alert-warning',
    'info' => 'alert-info',
];

$classes = ['alert', $variants[$variant] ?? 'alert-info'];
if ($dismissible) $classes[] = 'alert-dismissible';
if ($class) $classes[] = $class;
?>

<div class="{{ implode(' ', $classes) }}" {{ $attributes->merge(['class' => implode(' ', $classes), 'role' => 'alert']) }}>
    <div class="flex-1">
        {{ $slot }}
    </div>

    @if($dismissible)
        <button type="button" class="alert-close" aria-label="Dismiss">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>