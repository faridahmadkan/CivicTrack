<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') · CivicTrack</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-sand-50">
    @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
    <div class="min-h-screen lg:grid lg:grid-cols-[16rem_1fr]">
        <aside class="flex flex-col border-b border-line bg-white lg:min-h-screen lg:border-b-0 lg:border-r">
            <a href="{{ route('admin.home') }}" class="flex items-center gap-3 border-b border-line px-5 py-5">
                <span class="flex size-10 items-center justify-center rounded-xl bg-forest-900 text-white">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" aria-hidden="true"><path d="M4 18.5h16M6.5 18.5v-8l5.5-5 5.5 5v8M9.5 18.5v-5h5v5m-11-6.5 8.5-5 8.5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span><span class="block text-sm font-bold tracking-tight text-forest-950">CivicTrack</span><span class="mt-1 block text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Administration</span></span>
            </a>
            <nav class="flex gap-1 overflow-x-auto px-3 py-3 lg:flex-col lg:overflow-visible lg:px-3 lg:py-5" aria-label="Administration">
                <a href="{{ route('admin.home') }}" class="shrink-0 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-forest-50 hover:text-forest-900">Command center</a>
                @can('organization.manage')<a href="{{ route('admin.organization-units.index') }}" class="shrink-0 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-forest-50 hover:text-forest-900">Organization units</a>@endcan
                @can('registrations.manage')<a href="{{ route('admin.registration-controls.index') }}" class="shrink-0 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-forest-50 hover:text-forest-900">Registration settings</a>@endcan
                @can('viewAny', App\Models\WorkforceApplication::class)<a href="{{ route('admin.workforce-applications.index') }}" class="shrink-0 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-forest-50 hover:text-forest-900">Workforce applications</a>@endcan
                @can('catalog.manage')<a href="{{ route('admin.service-categories.index') }}" class="shrink-0 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-forest-50 hover:text-forest-900">Service catalog</a>@endcan
                @can('sla.manage')<a href="{{ route('admin.sla-policies.index') }}" class="shrink-0 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-forest-50 hover:text-forest-900">Service targets</a>@endcan
                @can('audit.view')<a href="{{ route('admin.audit-events.index') }}" class="shrink-0 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-forest-50 hover:text-forest-900">Audit trail</a>@endcan
                @can('viewAny', App\Models\CivicReport::class)<a href="{{ route('staff.reports.index') }}" class="shrink-0 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-forest-50 hover:text-forest-900">Report operations</a>@endcan
            </nav>
            <div class="mt-auto hidden border-t border-line p-4 lg:block">
                <a href="{{ route('public.home') }}" class="text-xs font-medium text-slate-600 transition hover:text-forest-900">View public site ↗</a>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="flex items-center justify-between gap-4 border-b border-line bg-white px-5 py-4 sm:px-8">
                <div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CivicTrack / Administration</p><p class="mt-1 text-sm font-semibold text-slate-800">@yield('eyebrow', 'Operations command center')</p></div>
                <div class="flex items-center gap-3">
                    <span class="hidden text-sm font-medium text-slate-600 sm:inline">{{ auth()->user()->name }}</span>
                    <a href="{{ route('account.notifications.index') }}" class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-sand-100">Notifications @if ($unreadNotifications > 0)<span class="ml-1 rounded-full bg-forest-900 px-1.5 py-0.5 text-[0.65rem] text-white">{{ $unreadNotifications }}</span>@endif</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-sand-100" type="submit">Sign out</button></form>
                </div>
            </header>
            <main class="mx-auto max-w-7xl px-5 py-7 sm:px-8 sm:py-9">
                <x-form-messages />
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
