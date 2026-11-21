@props(['title' => '', 'description' => '', 'action' => null, 'actionText' => '', 'actionVariant' => 'outline-primary', 'actionIcon' => null, 'class' => '', 'attributes' => []])

<div class="section-header {{ $class }}" {{ $attributes }}>
    <div>
        <h2 class="section-title">{{ $title }}</h2>
        @if($description)
            <p class="text-sm text-neutral-500 mt-0.5">{{ $description }}</p>
        @endif
    </div>

    @if($action)
        <x-button :variant="$actionVariant" :icon="$actionIcon" wire:click="{{ $action }}">{{ $actionText }}</x-button>
    @endif
</div>