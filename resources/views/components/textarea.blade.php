@props(['label' => '', 'name' => '', 'value' => '', 'placeholder' => '', 'required' => false, 'disabled' => false, 'error' => null, 'hint' => null, 'class' => '', 'id' => null, 'rows' => 4, 'attributes' => []])

<?php
$id = $id ?? $name;
$textareaClasses = ['form-textarea'];
if ($error) $textareaClasses[] = 'form-input-error';
if ($class) $textareaClasses[] = $class;
?>

<div class="form-group">
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger-500 ml-1" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        placeholder="{{ $placeholder }}"
        class="{{ implode(' ', $textareaClasses) }}"
        rows="{{ $rows }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => implode(' ', $textareaClasses)]) }}
        aria-invalid="{{ $error ? 'true' : 'false' }}"
        {{ $error ? 'aria-describedby="'.$id.'-error"' : '' }}
    >{{ old($name, $value) }}</textarea>

    @if($error)
        <p id="{{ $id }}-error" class="form-error" role="alert">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ $error }}
        </p>
    @endif

    @if($hint && !$error)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>