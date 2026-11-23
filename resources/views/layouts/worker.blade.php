<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#102a25">
    <title>@yield('title', 'Field workspace') · CivicTrack</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-sand-50">
    @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
    <div class="min-h-screen lg:grid lg:grid-cols-[16rem_1fr]">
        <aside class="flex flex-col border-b border-line bg-white lg:min-h-screen lg:border-b-0 lg:border-r">
            <a href="{{ route('worker.home') }}" class="flex items-center gap-3 border-b border-line px-5 py-5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><span class="flex size-10 items-center justify-center rounded-xl bg-forest-900 text-white"><svg viewBox="0 0 24 24" class="size-6" fill="none" aria-hidden="true"><path d="M12 21s7-5.1 7-12a7 7 0 1 0-14 0c0 6.9 7 12 7 12Z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="9" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg></span><span><span class="block text-sm font-bold text-forest-950">CivicTrack</span><span class="mt-0.5 block text-[0.63rem] font-semibold uppercase tracking-[0.15em] text-slate-500">Field workspace</span></span></a>
            <nav class="flex gap-1 overflow-x-auto px-3 py-3 lg:flex-col lg:overflow-visible lg:px-3 lg:py-5" aria-label="Worker workspace">
                <a href="{{ route('worker.home') }}" @if (request()->routeIs('worker.home')) aria-current="page" @endif class="flex shrink-0 items-center gap-2 rounded-lg border-l-4 px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 {{ request()->routeIs('worker.home') ? 'border-forest-800 bg-forest-50 font-semibold text-forest-950' : 'border-transparent text-slate-700 hover:bg-sand-100' }}">@if (request()->routeIs('worker.home'))<span aria-hidden="true">✓</span>@endif Overview</a>
                <a href="{{ route('worker.tasks.index') }}" @if (request()->routeIs('worker.tasks.*')) aria-current="page" @endif class="flex shrink-0 items-center gap-2 rounded-lg border-l-4 px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 {{ request()->routeIs('worker.tasks.*') ? 'border-forest-800 bg-forest-50 font-semibold text-forest-950' : 'border-transparent text-slate-700 hover:bg-sand-100' }}">@if (request()->routeIs('worker.tasks.*'))<span aria-hidden="true">✓</span>@endif My tasks</a>
                <a href="{{ route('account.notifications.index') }}" @if (request()->routeIs('account.notifications.*')) aria-current="page" @endif class="flex shrink-0 items-center gap-2 rounded-lg border-l-4 px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 {{ request()->routeIs('account.notifications.*') ? 'border-forest-800 bg-forest-50 font-semibold text-forest-950' : 'border-transparent text-slate-700 hover:bg-sand-100' }}">@if (request()->routeIs('account.notifications.*'))<span aria-hidden="true">✓</span>@endif Notifications @if ($unreadNotifications > 0)<span class="ml-auto rounded-full bg-forest-900 px-1.5 py-0.5 text-[0.65rem] text-white">{{ $unreadNotifications }}</span>@endif</a>
                <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif class="flex shrink-0 items-center gap-2 rounded-lg border-l-4 px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 {{ request()->routeIs('home') ? 'border-forest-800 bg-forest-50 font-semibold text-forest-950' : 'border-transparent text-slate-700 hover:bg-sand-100' }}">@if (request()->routeIs('home'))<span aria-hidden="true">✓</span>@endif Account overview</a>
            </nav>
            <div class="mt-auto hidden border-t border-line p-4 lg:block"><a href="{{ route('public.home') }}" class="text-xs font-medium text-slate-600 hover:text-forest-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">View public site</a></div>
        </aside>
        <div class="min-w-0">
            <header class="flex items-center justify-between gap-4 border-b border-line bg-white px-5 py-4 sm:px-8">
                <div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CivicTrack / Field workspace</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p></div>
                <div class="flex items-center gap-2"><x-public-home-link label="Home" class="px-2.5 sm:px-3" /><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="rounded-xl border border-line bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-sand-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Sign out</button></form></div>
            </header>
            <main class="mx-auto max-w-6xl px-5 py-7 sm:px-8 sm:py-10"><x-form-messages />@yield('content')</main>
            <footer class="mt-10 border-t border-line bg-white"><div class="mx-auto flex max-w-6xl justify-between gap-4 px-5 py-5 text-xs text-slate-500 sm:px-8"><span>Approved field workforce</span><a href="{{ route('privacy.notice') }}" class="hover:text-forest-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Privacy notice</a></div></footer>
        </div>
    </div>
</body>
</html>
