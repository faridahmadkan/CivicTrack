@props(['status', 'label' => null])

@php
    $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $statusLabel = $label ?? (method_exists($status, 'label') ? $status->label() : str($statusValue)->replace('_', ' ')->headline());
    $tone = match ($statusValue) {
        'submitted', 'under_review', 'needs_information', 'reopened', 'disputed', 'assigned', 'needs_rework', 'information_requested' => 'bg-amber-100 text-amber-950',
        'open', 'acknowledged', 'in_progress', 'accepted' => 'bg-sky-100 text-sky-950',
        'resolved', 'completed', 'approved' => 'bg-emerald-100 text-emerald-950',
        'rejected', 'cancelled', 'closed', 'duplicate', 'declined', 'reassigned', 'withdrawn' => 'bg-slate-200 text-slate-800',
        'submitted_for_review' => 'bg-violet-100 text-violet-950',
        default => 'bg-sand-100 text-slate-800',
    };
@endphp

<span {{ $attributes->class(['status-badge', $tone]) }}>{{ $statusLabel }}</span>
