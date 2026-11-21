@props(['title' => '', 'value' => '', 'icon' => null, 'iconVariant' => 'primary', 'trend' => null, 'trendLabel' => '', 'class' => '', 'attributes' => []])

<?php
$iconVariants = [
    'primary' => 'stat-card-icon-primary',
    'success' => 'stat-card-icon-success',
    'warning' => 'stat-card-icon-warning',
    'danger' => 'stat-card-icon-danger',
    'info' => 'stat-card-icon-info',
];

$iconClass = $iconVariants[$iconVariant] ?? 'stat-card-icon-primary';
?>

<div class="stat-card {{ $class }}" {{ $attributes }}>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm text-neutral-500 mb-1">{{ $title }}</p>
            <p class="text-3xl font-bold text-neutral-900">{{ $value }}</p>
            @if($trend !== null)
                <div class="flex items-center gap-1 mt-2">
                    <span class="text-sm font-medium {{ $trend >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ $trend >= 0 ? '+' : '' }}{{ $trend }}%
                    </span>
                    @if($trendLabel)
                        <span class="text-sm text-neutral-500">{{ $trendLabel }}</span>
                    @endif
                </div>
            @endif
        </div>
        @if($icon)
            <div class="{{ $iconClass }} flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path></svg>
            </div>
        @endif
    </div>
</div>