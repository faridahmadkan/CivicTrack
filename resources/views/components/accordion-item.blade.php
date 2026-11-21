@props(['class' => '', 'attributes' => []])

<div class="accordion-item {{ $class }}" {{ $attributes }}>
    {{ $slot }}
</div>