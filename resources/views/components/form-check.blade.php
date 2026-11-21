@props(['name' => '', 'id' => null, 'label' => '', 'value' => '1', 'checked' => false, 'disabled' => false, 'inline' => false, 'class' => '', 'attributes' => []])

<?php
$id = $id ?? $name;
$checkClasses = ['form-check'];
if ($inline) $checkClasses[] = 'd-inline-flex';
if ($class) $checkClasses[] = $class;
?>

<div class="{{ implode(' ', $checkClasses) }}">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        class="form-check-input"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'form-check-input']) }}
    >
    <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
</div>