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
    <header class="border-b border-line bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-8 lg:px-10">
            <a href="{{ route('home') }}" class="flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-xl bg-forest-900 text-white"><svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M4 18.5h16M6.5 18.5v-8l5.5-5 5.5 5v8M9.5 18.5v-5h5v5m-11-6.5 8.5-5 8.5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span><span class="block text-sm font-bold text-forest-950">CivicTrack</span><span class="mt-0.5 block text-[0.63rem] font-semibold uppercase tracking-[0.15em] text-slate-500">Citizen workspace</span></span></a>
            <nav class="flex items-center gap-1 sm:gap-3" aria-label="Citizen workspace">
                <a href="{{ route('citizen.home') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-sand-100">Overview</a>
                <a href="{{ route('citizen.reports.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-sand-100">My reports</a>
                <a href="{{ route('account.notifications.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-sand-100">Notifications @if ($unreadNotifications > 0)<span class="ml-1 rounded-full bg-forest-900 px-1.5 py-0.5 text-[0.65rem] text-white">{{ $unreadNotifications }}</span>@endif</a>
                <a href="{{ route('workforce.applications.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-forest-900 transition hover:bg-forest-50">Workforce applications</a>
            </nav>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-sand-100">Sign out</button></form>
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-10 lg:px-10">
        <x-form-messages />
        @yield('content')
    </main>
    <footer class="mt-10 border-t border-line bg-white"><div class="mx-auto flex max-w-7xl justify-between gap-4 px-5 py-5 text-xs text-slate-500 sm:px-8 lg:px-10"><span>Secure citizen account</span><a href="{{ route('privacy.notice') }}" class="hover:text-forest-900">Privacy notice</a></div></footer>
</body>
</html>
