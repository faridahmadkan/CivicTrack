@extends(auth()->user()->hasRole('administrator') ? 'layouts.admin' : (auth()->user()->hasRole('staff') ? 'layouts.staff' : (auth()->user()->hasRole('worker') ? 'layouts.worker' : (auth()->user()->hasRole('citizen') ? 'layouts.citizen' : 'layouts.auth'))))

@section('title', 'Notifications')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Your CivicTrack account</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">Notifications</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Service updates, workforce decisions, assignments, and accountability alerts sent to your account.</p>
        </div>
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('account.notifications.read-all') }}">
                @csrf
                <button class="rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-semibold text-forest-900 hover:bg-forest-50">Mark all read</button>
            </form>
        @endif
    </div>

    <form method="GET" action="{{ route('account.notifications.index') }}" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-[minmax(14rem,1fr)_14rem_auto_auto] sm:items-end">
        <label class="text-xs font-semibold text-slate-600">Search notifications
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800" placeholder="Report, task, application, or update">
        </label>
        <label class="text-xs font-semibold text-slate-600">Read status
            <select name="status" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All notifications</option><option value="unread" @selected(($filters['status'] ?? '') === 'unread')>Unread</option><option value="read" @selected(($filters['status'] ?? '') === 'read')>Read</option></select>
        </label>
        <button class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Search</button>
        <a href="{{ route('account.notifications.index') }}" class="rounded-lg border border-line bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-sand-50">Clear</a>
    </form>

    <section class="mt-6 space-y-3" aria-label="Account notifications">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $type = $data['type'] ?? class_basename($notification->type);
                $summary = match ($type) {
                    'report_sla_escalation' => 'Report '.($data['report_reference'] ?? '').' has an overdue '.implode(' and ', $data['targets'] ?? []).' target.',
                    'TaskAssignedNotification' => 'Task '.($data['task_number'] ?? '').' has been assigned to you.',
                    'WorkforceApplicationStatusChangedNotification' => 'Application '.($data['application_number'] ?? '').' is now '.($data['status_label'] ?? 'updated').'.',
                    'CivicReportStatusChangedNotification' => 'Report '.($data['public_reference'] ?? '').' is now '.($data['status_label'] ?? 'updated').'.',
                    default => 'You have a CivicTrack account update.',
                };
            @endphp
            <article class="rounded-2xl border {{ $notification->read_at ? 'border-line bg-white' : 'border-forest-200 bg-forest-50/40' }} p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm {{ $notification->read_at ? 'font-medium text-slate-800' : 'font-semibold text-forest-950' }}">{{ $summary }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $notification->created_at->format('M j, Y · g:i A') }} @if ($notification->read_at) · Read {{ $notification->read_at->format('M j, Y · g:i A') }} @else · Unread @endif</p>
                    </div>
                    @if (! $notification->read_at)
                        <form method="POST" action="{{ route('account.notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-sand-50">Mark read</button>
                        </form>
                    @endif
                </div>
                @if (! empty($data['url']))
                    <a href="{{ $data['url'] }}" class="mt-3 inline-flex text-sm font-semibold text-forest-800 underline decoration-forest-200 underline-offset-2 hover:text-forest-950">Open related record →</a>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-line bg-white p-8 text-center sm:p-12">
                <h2 class="text-base font-semibold text-slate-900">{{ filled($filters['q'] ?? null) || filled($filters['status'] ?? null) ? 'No notifications match these filters' : 'No notifications yet' }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ filled($filters['q'] ?? null) || filled($filters['status'] ?? null) ? 'Try another search term or read status.' : 'Updates will appear here when a service team changes a report, reviews an application, or assigns field work.' }}</p>
            </div>
        @endforelse
    </section>
    <div class="mt-5">{{ $notifications->links() }}</div>
    <a href="{{ route('home') }}" class="mt-5 inline-flex text-sm font-semibold text-forest-800 hover:text-forest-950">Return to your workspace →</a>
@endsection
