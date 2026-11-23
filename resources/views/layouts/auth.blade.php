<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Secure account') · CivicTrack</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-sand-50">
    <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(25rem,0.88fr)]">
        <aside class="relative hidden overflow-hidden bg-forest-950 px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between xl:px-20">
            <div class="absolute -right-32 top-1/4 size-[29rem] rounded-full border border-white/10"></div>
            <div class="absolute -right-20 top-[29%] size-[21rem] rounded-full border border-white/10"></div>
            <div class="absolute -right-10 top-[34%] size-[11rem] rounded-full border border-white/10"></div>
            <a href="{{ route('public.home') }}" class="relative z-10 flex w-fit items-center gap-3 rounded-lg">
                <span class="flex size-10 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" aria-hidden="true">
                        <path d="M4 18.5h16M6.5 18.5v-8l5.5-5 5.5 5v8M9.5 18.5v-5h5v5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="m3.5 8.5 8.5-5 8.5 5" stroke="#c8e3c5" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-lg font-bold tracking-tight">CivicTrack</span>
            </a>

            <div class="relative z-10 max-w-xl py-16">
                <p class="mb-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100/80">
                    <span class="size-1.5 rounded-full bg-emerald-200"></span>
                    Built for civic service
                </p>
                <h1 class="max-w-lg text-4xl font-semibold leading-[1.12] tracking-tight xl:text-5xl">A clearer path from community concern to public action.</h1>
                <p class="mt-6 max-w-md text-base leading-7 text-emerald-50/75">Secure access for residents and the people who coordinate essential community services.</p>
            </div>

            <p class="relative z-10 text-xs text-emerald-50/55">CivicTrack is an independent civic technology platform.</p>
        </aside>

        <main class="flex min-h-screen flex-col px-5 py-6 sm:px-10 lg:px-14 xl:px-20">
            <div class="flex items-center justify-between lg:justify-end">
                <a href="{{ route('public.home') }}" class="inline-flex items-center gap-2 text-sm font-bold text-forest-900 lg:hidden">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-forest-900 text-white">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M4 18.5h16M6.5 18.5v-8l5.5-5 5.5 5v8M9.5 18.5v-5h5v5m-11-6.5 8.5-5 8.5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    CivicTrack
                </a>
                <a href="{{ route('public.home') }}" class="hidden rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-sand-100 hover:text-forest-900 lg:inline-flex">← Back to CivicTrack</a>
            </div>
            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-12">
                @yield('content')
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5 text-xs text-slate-500">
                <span>Secure account access</span>
                <a href="{{ route('privacy.notice') }}" class="font-medium transition hover:text-forest-900">Privacy notice</a>
            </div>
        </main>
    </div>
</body>
</html>
