@props(['variant' => 'primary', 'size' => 'md', 'disabled' => false, 'loading' => false, 'icon' => null, 'iconPosition' => 'left', 'type' => 'button', 'class' => '', 'attributes' => []])

<?php
$variants = [
    'primary' => 'btn-primary',
    'secondary' => 'btn-secondary',
    'outline' => 'btn-outline-primary',
    'outline-danger' => 'btn-outline-danger',
    'danger' => 'btn-danger',
    'success' => 'btn-success',
    'warning' => 'btn-warning',
    'ghost' => 'btn-ghost',
];

$sizes = [
    'sm' => 'btn-sm',
    'md' => 'btn-md',
    'lg' => 'btn-lg',
];

$classes = ['btn', $variants[$variant] ?? 'btn-primary', $sizes[$size] ?? 'btn-md'];
if ($disabled) $classes[] = 'disabled';
if ($loading) $classes[] = 'loading';
if ($class) $classes[] = $class;
?>

<button
    type="{{ $type }}"
    class="{{ implode(' ', $classes) }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => implode(' ', $classes)]) }}
>
    {{ $loading
        ? '<svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
        : ($icon && $iconPosition === 'left' ? "<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"{$icon}\"></path></svg>" : '')
    }}
    {{ $slot }}
    {{ $icon && $iconPosition === 'right' ? "<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"{$icon}\"></path></svg>" : '' }}
</button>