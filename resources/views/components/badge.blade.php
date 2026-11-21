@props(['variant' => 'neutral', 'dot' => false, 'class' => '', 'attributes' => []])

<?php
$variants = [
    'primary' => 'badge-primary',
    'success' => 'badge-success',
    'warning' => 'badge-warning',
    'danger' => 'badge-danger',
    'info' => 'badge-info',
    'neutral' => 'badge-neutral',
    'outline-primary' => 'badge-outline-primary',
];

$classes = ['badge', $variants[$variant] ?? 'badge-neutral'];
if ($dot) $classes[] = 'badge-dot';
if ($class) $classes[] = $class;
?>

<span class="{{ implode(' ', $classes) }}" {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    {{ $slot }}
</span>