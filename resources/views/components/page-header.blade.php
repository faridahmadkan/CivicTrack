@props(['title' => '', 'subtitle' => '', 'action' => null, 'actionText' => '', 'actionVariant' => 'primary', 'actionIcon' => null, 'class' => '', 'attributes' => []])

<div class="page-header {{ $class }}" {{ $attributes }}>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            @if($subtitle)
                <p class="page-subtitle">{{ $subtitle }}</p>
            @endif
        </div>

        @if($action)
            <div class="mt-4 sm:mt-0">
                <x-button :variant="$actionVariant" :icon="$actionIcon" wire:click="{{ $action }}">{{ $actionText }}</x-button>
            </div>
        @endif
    </div>
</div>