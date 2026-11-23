<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Citizen workspace') · CivicTrack</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-sand-50">
    @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
    @php($pendingWorkforceApplications = auth()->user()->workforceApplications()->whereIn('status', [App\Enums\WorkforceApplicationStatus::Submitted->value, App\Enums\WorkforceApplicationStatus::UnderReview->value, App\Enums\WorkforceApplicationStatus::InformationRequested->value])->with('requestedUnit')->orderByDesc('submitted_at')->limit(3)->get())
    <div class="min-h-screen lg:grid lg:grid-cols-[16rem_1fr]">
        <aside class="flex flex-col border-b border-line bg-white lg:min-h-screen lg:border-b-0 lg:border-r">
            <a href="{{ route('citizen.home') }}" class="flex items-center gap-3 border-b border-line px-5 py-5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><span class="flex size-9 items-center justify-center rounded-xl bg-forest-900 text-white"><svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M4 18.5h16M6.5 18.5v-8l5.5-5 5.5 5v8M9.5 18.5v-5h5v5m-11-6.5 8.5-5 8.5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span><span class="block text-sm font-bold text-forest-950">CivicTrack</span><span class="mt-0.5 block text-[0.63rem] font-semibold uppercase tracking-[0.15em] text-slate-500">Citizen workspace</span></span></a>
            <nav class="flex gap-1 overflow-x-auto px-3 py-3 lg:flex-col lg:overflow-visible lg:px-3 lg:py-5" aria-label="Citizen workspace">
                <a href="{{ route('citizen.home') }}" @if (request()->routeIs('citizen.home')) aria-current="page" @endif class="flex shrink-0 items-center gap-2 rounded-lg border-l-4 px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 {{ request()->routeIs('citizen.home') ? 'border-forest-800 bg-forest-50 font-semibold text-forest-950' : 'border-transparent text-slate-700 hover:bg-sand-100' }}">@if (request()->routeIs('citizen.home'))<span aria-hidden="true">✓</span>@endif Citizen dashboard</a>
                <a href="{{ route('citizen.reports.index') }}" @if (request()->routeIs('citizen.reports.*')) aria-current="page" @endif class="flex shrink-0 items-center gap-2 rounded-lg border-l-4 px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 {{ request()->routeIs('citizen.reports.*') ? 'border-forest-800 bg-forest-50 font-semibold text-forest-950' : 'border-transparent text-slate-700 hover:bg-sand-100' }}">@if (request()->routeIs('citizen.reports.*'))<span aria-hidden="true">✓</span>@endif My reports</a>
                <a href="{{ route('workforce.applications.index') }}" @if (request()->routeIs('workforce.applications.*')) aria-current="page" @endif class="flex shrink-0 items-center gap-2 rounded-lg border-l-4 px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 {{ request()->routeIs('workforce.applications.*') ? 'border-forest-800 bg-forest-50 font-semibold text-forest-950' : 'border-transparent text-forest-900 hover:bg-forest-50' }}">@if (request()->routeIs('workforce.applications.*'))<span aria-hidden="true">✓</span>@endif Workforce applications</a>
                <a href="{{ route('account.notifications.index') }}" @if (request()->routeIs('account.notifications.*')) aria-current="page" @endif class="flex shrink-0 items-center gap-2 rounded-lg border-l-4 px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 {{ request()->routeIs('account.notifications.*') ? 'border-forest-800 bg-forest-50 font-semibold text-forest-950' : 'border-transparent text-slate-700 hover:bg-sand-100' }}">@if (request()->routeIs('account.notifications.*'))<span aria-hidden="true">✓</span>@endif Notifications @if ($unreadNotifications > 0)<span class="ml-auto rounded-full bg-forest-900 px-1.5 py-0.5 text-[0.65rem] text-white">{{ $unreadNotifications }}</span>@endif</a>
                <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif class="flex shrink-0 items-center gap-2 rounded-lg border-l-4 px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 {{ request()->routeIs('home') ? 'border-forest-800 bg-forest-50 font-semibold text-forest-950' : 'border-transparent text-slate-700 hover:bg-sand-100' }}">@if (request()->routeIs('home'))<span aria-hidden="true">✓</span>@endif Account overview</a>
            </nav>
            <div class="mt-auto hidden border-t border-line p-4 lg:block"><a href="{{ route('public.home') }}" class="text-xs font-medium text-slate-600 hover:text-forest-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">View public site</a></div>
        </aside>
        <div class="min-w-0">
            <header class="flex items-center justify-between gap-4 border-b border-line bg-white px-5 py-4 sm:px-8">
                <div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CivicTrack / Citizen workspace</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p></div>
                <div class="flex items-center gap-2"><x-public-home-link label="Home" class="px-2.5 sm:px-3" /><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="rounded-xl border border-line bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-sand-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Sign out</button></form></div>
            </header>
            <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-10 lg:px-10">
                <x-form-messages />
                @if ($pendingWorkforceApplications->isNotEmpty())
                    <section class="mb-6 rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-white p-4 shadow-sm sm:p-5" aria-labelledby="pending-workforce-heading">
                        <div class="flex items-start gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-900" aria-hidden="true"><svg viewBox="0 0 20 20" class="size-5" fill="none"><circle cx="10" cy="10" r="7.25" stroke="currentColor" stroke-width="1.5"/><path d="M10 6v4l2.5 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></span>
                            <div class="min-w-0 flex-1"><h2 id="pending-workforce-heading" class="text-sm font-semibold text-amber-950">Your workforce application is awaiting administrator review</h2><p class="mt-1 text-sm leading-6 text-amber-900">You can continue using your citizen account while your application is pending. Staff and field-worker tools unlock only after approval.</p>
                                <ul class="mt-3 space-y-2">@foreach ($pendingWorkforceApplications as $application)<li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-200/80 bg-white/80 px-3 py-2"><span class="text-xs font-medium text-slate-700">{{ ucfirst($application->application_type->value) }} · {{ $application->requestedUnit?->name ?? 'Service unit' }} · {{ $application->status->label() }}</span><a href="{{ route('workforce.applications.show', $application) }}" class="group inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-semibold text-forest-800 transition hover:bg-forest-50">View application <svg viewBox="0 0 16 16" class="size-3.5 transition-transform group-hover:translate-x-0.5" fill="none" aria-hidden="true"><path d="M3 8h10m0 0L9 4m4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg></a></li>@endforeach</ul>
                            </div>
                        </div>
                    </section>
                @endif
                @yield('content')
            </main>
            <footer class="mt-10 border-t border-line bg-white"><div class="mx-auto flex max-w-7xl justify-between gap-4 px-5 py-5 text-xs text-slate-500 sm:px-8 lg:px-10"><span>Secure citizen account</span><a href="{{ route('privacy.notice') }}" class="hover:text-forest-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Privacy notice</a></div></footer>
        </div>
    </div>
</body>
</html>
