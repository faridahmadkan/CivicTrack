@props(['id' => '', 'title' => '', 'size' => 'lg', 'closeable' => true, 'class' => '', 'attributes' => []])

<?php
$sizes = [
    'sm' => 'max-w-md',
    'md' => 'max-w-lg',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl',
    'full' => 'max-w-full mx-4',
];

$modalId = $id ?: 'modal-' . uniqid();
$sizeClass = $sizes[$size] ?? 'max-w-lg';
?>

<div class="modal-overlay" id="{{ $modalId }}-overlay" data-modal-overlay></div>

<div class="modal {{ $sizeClass }} {{ $class }}" id="{{ $modalId }}" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title" {{ $attributes }}>
    <div class="modal-header">
        <h2 id="{{ $modalId }}-title" class="modal-title">{{ $title }}</h2>
        @if($closeable)
            <button type="button" class="modal-close" data-modal-close aria-label="Close modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        @endif
    </div>
    <div class="modal-body">
        {{ $slot }}
    </div>
</div>