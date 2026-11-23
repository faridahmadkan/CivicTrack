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
    <header class="border-b border-line bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-8">
            <a href="{{ route('worker.home') }}" class="flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-forest-900 text-white"><svg viewBox="0 0 24 24" class="size-6" fill="none" aria-hidden="true"><path d="M12 21s7-5.1 7-12a7 7 0 1 0-14 0c0 6.9 7 12 7 12Z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="9" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg></span><span><span class="block text-sm font-bold text-forest-950">CivicTrack</span><span class="mt-0.5 block text-[0.63rem] font-semibold uppercase tracking-[0.15em] text-slate-500">Field workspace</span></span></a>
            <nav class="flex items-center gap-1" aria-label="Worker workspace"><a href="{{ route('worker.tasks.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-sand-100">My tasks</a><a href="{{ route('account.notifications.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-sand-100">Notifications @if ($unreadNotifications > 0)<span class="ml-1 rounded-full bg-forest-900 px-1.5 py-0.5 text-[0.65rem] text-white">{{ $unreadNotifications }}</span>@endif</a><a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-sand-100">Account</a></nav>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-sand-100">Sign out</button></form>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-5 py-7 sm:px-8 sm:py-10"><x-form-messages />@yield('content')</main>
    <footer class="mt-10 border-t border-line bg-white"><div class="mx-auto flex max-w-6xl justify-between gap-4 px-5 py-5 text-xs text-slate-500 sm:px-8"><span>Approved field workforce</span><a href="{{ route('privacy.notice') }}" class="hover:text-forest-900">Privacy notice</a></div></footer>
</body>
</html>
