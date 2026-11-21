@props(['variant' => 'default', 'hover' => false, 'class' => ''])

<?php
$variants = [
    'default' => 'card',
    'hover' => 'card-hover',
];

$classes = [$variants[$variant] ?? 'card'];
if ($hover && $variant === 'default') $classes[] = 'card-hover';
if ($class) $classes[] = $class;
?>

<div {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    @if(isset($header) && $header)
        <div class="card-header">
            {{ $header }}
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if(isset($footer) && $footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>