@props(['src' => null, 'alt' => '', 'name' => '', 'size' => 'md', 'class' => '', 'attributes' => []])

<?php
$sizes = [
    'xs' => 'avatar-xs',
    'sm' => 'avatar-sm',
    'md' => 'avatar-md',
    'lg' => 'avatar-lg',
    'xl' => 'avatar-xl',
];

$sizeClass = $sizes[$size] ?? 'avatar-md';
$classes = ['avatar', $sizeClass];
if ($class) $classes[] = $class;
?>

@if($src)
    <div class="{{ implode(' ', $classes) }}" {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
        <img src="{{ $src }}" alt="{{ $alt }}" class="avatar-img" loading="lazy">
    </div>
@else
    <div class="{{ implode(' ', $classes) }}" {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
        {{ $name ? strtoupper(collect(explode(' ', $name))->map(fn($w) => $w[0])->take(2)->implode('')) : '?' }}
    </div>
@endif