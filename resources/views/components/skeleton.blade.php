@props(['type' => 'text', 'lines' => 1, 'class' => '', 'attributes' => []])

<?php
$types = [
    'text' => 'skeleton-text',
    'title' => 'skeleton-title',
    'card' => 'skeleton-card',
    'avatar' => 'skeleton-avatar',
    'button' => 'skeleton-btn',
];

$baseClass = $types[$type] ?? 'skeleton-text';
$classes = ['skeleton', $baseClass];
if ($class) $classes[] = $class;
?>

@for($i = 0; $i < $lines; $i++)
    <div class="{{ implode(' ', $classes) }}" {{ $i === 0 ? $attributes->merge(['class' => implode(' ', $classes)]) : '' }}></div>
    @if($i < $lines - 1 && $type === 'text')
        <div class="h-4"></div>
    @endif
@endfor