@props(['icon' => null, 'title' => '', 'description' => '', 'action' => null, 'actionText' => '', 'actionVariant' => 'primary', 'class' => '', 'attributes' => []])

<div class="empty-state {{ $class }}" {{ $attributes }}>
    @if($icon)
        <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"></path></svg>
    @endif

    @if($title)
        <h3 class="empty-state-title">{{ $title }}</h3>
    @endif

    @if($description)
        <p class="empty-state-description">{{ $description }}</p>
    @endif

    @if($action)
        <x-button :variant="$actionVariant" wire:click="{{ $action }}">{{ $actionText }}</x-button>
    @endif
</div>