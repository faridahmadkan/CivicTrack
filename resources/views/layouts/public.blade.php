<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#102a25">
    <title>@yield('title', 'CivicTrack') · CivicTrack</title>
    <meta name="description" content="CivicTrack connects residents and public service teams around civic issues and accountable follow-through.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <header class="relative z-10 border-b border-line bg-white/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-4 sm:px-8 lg:px-10">
            <a href="{{ route('public.home') }}" class="flex items-center gap-3 rounded-lg" aria-label="CivicTrack home">
                <span class="flex size-10 items-center justify-center rounded-xl bg-forest-900 text-white shadow-sm">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" aria-hidden="true">
                        <path d="M4 18.5h16M6.5 18.5v-8l5.5-5 5.5 5v8M9.5 18.5v-5h5v5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="m3.5 8.5 8.5-5 8.5 5" stroke="#c8e3c5" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="flex flex-col leading-tight">
                    <span class="text-[1.05rem] font-bold tracking-tight text-forest-950">CivicTrack</span>
                    <span class="mt-1 text-[0.66rem] font-semibold uppercase tracking-[0.15em] text-slate-500">Community services</span>
                </span>
            </a>

            <nav class="flex items-center gap-2 sm:gap-4" aria-label="Main navigation">
                <a href="{{ route('public.home') }}#how-it-works" class="hidden rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-sand-100 hover:text-forest-900 sm:inline-flex">How it works</a>
                <a href="{{ route('public.map.index') }}" @if (request()->routeIs('public.map.*')) aria-current="page" @endif class="hidden rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-sand-100 hover:text-forest-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 sm:inline-flex">@if (request()->routeIs('public.map.*'))<span aria-hidden="true">✓ </span>@endif Public map</a>
                <a href="{{ route('privacy.notice') }}" @if (request()->routeIs('privacy.*')) aria-current="page" @endif class="hidden rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-sand-100 hover:text-forest-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700 md:inline-flex">@if (request()->routeIs('privacy.*'))<span aria-hidden="true">✓ </span>@endif Privacy</a>
                <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-forest-900 transition hover:bg-forest-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Sign in</a>
                @if ($citizenRegistrationOpen ?? false)
                    <a href="{{ route('register') }}" class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-800">Create account</a>
                @endif
            </nav>
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="border-t border-line bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-7 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10">
            <p>© {{ now()->year }} CivicTrack. A civic service coordination platform.</p>
            <div class="flex items-center gap-5">
                <a href="{{ route('privacy.notice') }}" class="transition hover:text-forest-900">Privacy notice</a>
                <a href="{{ route('login') }}" class="transition hover:text-forest-900">Account sign in</a>
            </div>
        </div>
    </footer>
</body>
</html>
