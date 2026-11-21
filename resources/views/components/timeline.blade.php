@props(['items' => [], 'class' => '', 'attributes' => []])

<div class="timeline {{ $class }}" {{ $attributes }}>
    @foreach($items as $index => $item)
        <?php
        $isLast = $index === count($items) - 1;
        $dotClass = 'timeline-dot';
        if ($item['completed'] ?? false) $dotClass .= ' completed';
        elseif ($item['current'] ?? false) $dotClass .= ' current';
        else $dotClass .= ' pending';
        ?>
        <div class="timeline-item {{ ($item['completed'] ?? false) ? 'completed' : '' }}">
            <div class="{{ $dotClass }}"></div>
            <div class="timeline-content">
                @if(isset($item['time']))
                    <div class="timeline-time">{{ $item['time'] }}</div>
                @endif
                <div class="timeline-title">{{ $item['title'] }}</div>
                @if(isset($item['description']))
                    <div class="timeline-description">{{ $item['description'] }}</div>
                @endif
                @if(isset($item['meta']))
                    <div class="timeline-meta text-xs text-neutral-500 mt-1">{{ $item['meta'] }}</div>
                @endif
            </div>
        </div>
    @endforeach
</div>