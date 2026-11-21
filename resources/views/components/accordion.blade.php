@props(['class' => '', 'attributes' => []])

<div class="accordion {{ $class }}" {{ $attributes }}>
    {{ $slot }}
</div>