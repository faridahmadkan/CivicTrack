@props(['steps' => [], 'currentStep' => 0, 'class' => '', 'attributes' => []])

<div class="progress-steps {{ $class }}" {{ $attributes }} role="list" aria-label="Progress steps">
    @foreach($steps as $index => $step)
        <?php
        $stepNum = $index + 1;
        $circleClass = 'progress-step-circle';
        $labelClass = 'progress-step-label';
        if ($stepNum < $currentStep) {
            $circleClass .= ' completed';
            $labelClass .= ' completed';
        } elseif ($stepNum === $currentStep) {
            $circleClass .= ' current';
            $labelClass .= ' current';
        } else {
            $circleClass .= ' pending';
            $labelClass .= ' pending';
        }
        ?>
        <div class="progress-step {{ $index === count($steps) - 1 ? '' : '' }}" role="listitem">
            <div class="{{ $circleClass }}" aria-current="{{ $stepNum === $currentStep ? 'step' : 'false' }}">
                @if($stepNum < $currentStep)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @else
                    {{ $stepNum }}
                @endif
            </div>
            <span class="{{ $labelClass }}">{{ $step }}</span>
        </div>
    @endforeach
</div>