@props(['label' => '', 'name' => '', 'accept' => 'image/*', 'multiple' => false, 'maxFiles' => 5, 'maxSize' => 2048, 'error' => null, 'hint' => null, 'class' => '', 'id' => null, 'preview' => true, 'attributes' => []])

<?php
$id = $id ?? $name;
$inputClasses = ['form-file'];
if ($error) $inputClasses[] = 'form-input-error';
if ($class) $inputClasses[] = $class;
?>

<div class="form-group">
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            <span class="text-danger-500 ml-1" aria-hidden="true">*</span>
        </label>
    @endif

    <div class="upload-zone" data-upload-zone>
        <svg class="upload-zone-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        <p class="upload-zone-title">Drag & drop files here, or click to browse</p>
        <p class="upload-zone-description">{{ $hint ?: 'Supports: ' . $accept }}</p>
        <input
            type="file"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            id="{{ $id }}"
            accept="{{ $accept }}"
            class="sr-only"
            {{ $multiple ? 'multiple' : '' }}
            {{ $attributes->merge(['class' => 'sr-only']) }}
            aria-invalid="{{ $error ? 'true' : 'false' }}"
            {{ $error ? 'aria-describedby="'.$id.'-error"' : '' }}
        >
    </div>

    @if($preview && $multiple)
        <div class="file-previews flex flex-wrap gap-2 mt-3" role="list" aria-label="File previews"></div>
    @endif

    @if($error)
        <p id="{{ $id }}-error" class="form-error" role="alert">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ $error }}
        </p>
    @endif
</div>