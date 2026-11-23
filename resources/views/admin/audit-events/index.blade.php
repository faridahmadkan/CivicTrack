@extends('layouts.admin')

@section('title', 'Audit trail')
@section('eyebrow', 'Accountability and system history')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Append-only records</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Audit trail</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Review attributed administrative and service decisions. Audit records cannot be edited or deleted through the application.</p>
        </div>
        <span class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">{{ number_format($events->total()) }} matching events</span>
    </div>

    <form method="GET" action="{{ route('admin.audit-events.index') }}" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-2 xl:grid-cols-4">
        <label class="text-xs font-semibold text-slate-600">Event
            <input name="event_key" value="{{ $filters['event_key'] ?? '' }}" maxlength="120" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800" placeholder="report_sla.escalated">
        </label>
        <label class="text-xs font-semibold text-slate-600">Actor
            <select name="actor_id" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800">
                <option value="">All actors</option>
                @foreach ($actors as $actor)
                    <option value="{{ $actor->id }}" @selected(($filters['actor_id'] ?? '') == $actor->id)>{{ $actor->name }} · {{ $actor->email }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-xs font-semibold text-slate-600">From
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800">
        </label>
        <label class="text-xs font-semibold text-slate-600">To
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800">
        </label>
        <div class="flex items-center gap-2 xl:col-span-4">
            <button class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Apply filters</button>
            <a href="{{ route('admin.audit-events.index') }}" class="rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-sand-50">Clear</a>
        </div>
    </form>

    <section class="mt-5 overflow-hidden rounded-2xl border border-line bg-white shadow-sm" aria-label="Audit events">
        @forelse ($events as $event)
            <article class="border-b border-line p-5 last:border-0 sm:px-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-mono text-sm font-semibold text-forest-950">{{ $event->event_key }}</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ $event->actor?->name ?? 'System' }}{{ $event->actor?->email ? ' · '.$event->actor->email : '' }} · {{ $event->occurred_at->format('M j, Y · g:i:s A') }}</p>
                    </div>
                    <span class="rounded-full bg-sand-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $event->subject_type ? class_basename($event->subject_type).' #'.$event->subject_id : 'System event' }}</span>
                </div>
                @if ($event->properties)
                    <details class="mt-3 rounded-xl bg-sand-50 p-3">
                        <summary class="cursor-pointer text-xs font-semibold text-slate-700">Event properties</summary>
                        <pre class="mt-3 overflow-x-auto whitespace-pre-wrap break-words text-xs leading-5 text-slate-600">{{ json_encode($event->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                @endif
                <details class="mt-2">
                    <summary class="cursor-pointer text-xs font-semibold text-slate-500">Request metadata</summary>
                    <dl class="mt-2 grid gap-2 text-xs sm:grid-cols-2">
                        <div><dt class="font-semibold text-slate-500">IP address</dt><dd class="mt-1 font-mono text-slate-700">{{ $event->ip_address ?? 'Unavailable' }}</dd></div>
                        <div><dt class="font-semibold text-slate-500">User agent</dt><dd class="mt-1 break-all text-slate-700">{{ $event->user_agent ?? 'Unavailable' }}</dd></div>
                    </dl>
                </details>
            </article>
        @empty
            <div class="p-8 text-center sm:p-12">
                <h2 class="text-base font-semibold text-slate-900">No audit events match these filters</h2>
                <p class="mt-2 text-sm text-slate-600">Try a different event, actor, or date range.</p>
            </div>
        @endforelse
        @if ($events->hasPages())
            <div class="border-t border-line px-5 py-4 sm:px-6">{{ $events->links() }}</div>
        @endif
    </section>
@endsection
