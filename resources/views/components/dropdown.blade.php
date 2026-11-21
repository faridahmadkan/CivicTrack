@props(['trigger' => null, 'align' => 'right', 'class' => '', 'attributes' => []])

<div class="dropdown {{ $class }}" {{ $attributes }}>
    @if($trigger)
        <div data-dropdown-trigger>
            {{ $trigger }}
        </div>
    @else
        <slot name="trigger"></slot>
    @endif

    <div class="dropdown-menu {{ $align === 'left' ? 'left-0 right-auto' : '' }}" role="menu">
        {{ $slot }}
    </div>
</div>