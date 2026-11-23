@extends('layouts.public')

@section('title', 'Civic services, connected')

@section('content')
    <section class="relative overflow-hidden bg-forest-950 text-white">
        <div class="absolute inset-0 opacity-30" aria-hidden="true">
            <svg class="absolute -right-24 -top-28 h-[42rem] w-[42rem]" viewBox="0 0 700 700" fill="none">
                <circle cx="350" cy="350" r="330" stroke="#c8e3c5" stroke-opacity=".35"/>
                <circle cx="350" cy="350" r="255" stroke="#c8e3c5" stroke-opacity=".3"/>
                <circle cx="350" cy="350" r="180" stroke="#c8e3c5" stroke-opacity=".25"/>
                <path d="M30 384c106-93 151 46 250-22 113-78 154 64 224 6 69-56 106-67 164-8M80 174c108 47 142 123 249 63 85-48 174-25 240 31M202 656c19-113 132-96 153-197 23-108-10-166 70-239" stroke="#c8e3c5" stroke-opacity=".42" stroke-width="2"/>
                <circle cx="455" cy="270" r="9" fill="#d8e7bb"/>
                <circle cx="455" cy="270" r="21" stroke="#d8e7bb" stroke-opacity=".5"/>
            </svg>
        </div>
        <div class="relative mx-auto grid max-w-7xl gap-12 px-5 py-20 sm:px-8 sm:py-24 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:px-10 lg:py-28">
            <div class="max-w-2xl">
                <p class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3.5 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-emerald-100/90">
                    <span class="size-1.5 rounded-full bg-emerald-200"></span>
                    Civic services, connected
                </p>
                <h1 class="text-4xl font-semibold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">A better way to care for the places we share.</h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-emerald-50/75">CivicTrack is designed to help residents raise local concerns and help public service teams coordinate a clear, accountable response.</p>

                <div class="mt-9 flex flex-wrap items-center gap-3">
                    <a href="{{ route('public.reports.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-forest-950 shadow-sm transition hover:bg-emerald-50">Report a civic issue</a>
                    <a href="{{ route('public.map.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/25 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Explore published reports</a>
                    @if ($citizenRegistrationOpen)
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/25 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                            Create a citizen account
                            <svg viewBox="0 0 20 20" class="size-4" fill="none" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @else
                        <span class="rounded-lg border border-white/20 bg-white/[0.07] px-5 py-3 text-sm font-medium text-emerald-50">Citizen registration is currently closed</span>
                    @endif
                    <a href="#how-it-works" class="rounded-lg px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Explore the approach</a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-md lg:ml-auto lg:mr-0" aria-hidden="true">
                <div class="absolute -inset-8 rounded-[2rem] bg-emerald-100/[0.04] blur-2xl"></div>
                <div class="relative rounded-2xl border border-white/15 bg-white/[0.08] p-5 shadow-2xl shadow-black/20 backdrop-blur-sm sm:p-6">
                    <div class="flex items-center justify-between border-b border-white/10 pb-4">
                        <div>
                            <p class="text-sm font-semibold">A shared civic workspace</p>
                            <p class="mt-1 text-xs text-emerald-50/60">Designed around people and place</p>
                        </div>
                        <span class="flex size-9 items-center justify-center rounded-xl bg-emerald-100/10 text-emerald-100">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none"><path d="M12 21s7-5.1 7-12a7 7 0 1 0-14 0c0 6.9 7 12 7 12Z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="9" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg>
                        </span>
                    </div>
                    <div class="relative mt-5 h-52 overflow-hidden rounded-xl border border-white/10 bg-[#173a32]">
                        <svg viewBox="0 0 440 220" class="absolute inset-0 h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none">
                            <path d="M-16 46c77 15 87 89 164 80 53-6 74-72 139-64 50 7 81 54 169 28M-7 168c68-46 116-34 154-12 46 26 63 57 119 44 53-13 87-69 184-50M63-19c17 66 70 78 67 131-2 48-40 57-26 113M301-9c-42 50-43 93-18 124 26 32 82 20 94 105" stroke="#b8d2c2" stroke-opacity=".4" stroke-width="2"/>
                            <path d="m16 106 63-21 44 29 49-54 53 24 43-39 69 47 54-17 37 28" stroke="#d2e1c6" stroke-opacity=".36" stroke-width="1.2" stroke-dasharray="4 6"/>
                            <circle cx="257" cy="105" r="17" fill="#d2e1c6" fill-opacity=".14"/><circle cx="257" cy="105" r="6" fill="#d2e1c6"/>
                            <circle cx="172" cy="60" r="4" fill="#aac9b4"/><circle cx="334" cy="92" r="4" fill="#aac9b4"/><circle cx="124" cy="114" r="4" fill="#aac9b4"/>
                        </svg>
                        <div class="absolute bottom-3 left-3 rounded-lg border border-white/10 bg-forest-950/80 px-3 py-2 text-[0.68rem] font-medium tracking-wide text-emerald-50/80">A place-based service model</div>
                    </div>
                    <div class="mt-4 grid grid-cols-3 divide-x divide-white/10">
                        <div class="px-2 text-center"><p class="text-xs font-semibold text-white">Residents</p><p class="mt-1 text-[0.68rem] text-emerald-50/55">Share concerns</p></div>
                        <div class="px-2 text-center"><p class="text-xs font-semibold text-white">Service teams</p><p class="mt-1 text-[0.68rem] text-emerald-50/55">Coordinate work</p></div>
                        <div class="px-2 text-center"><p class="text-xs font-semibold text-white">Communities</p><p class="mt-1 text-[0.68rem] text-emerald-50/55">See progress</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-line bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 py-8 sm:grid-cols-[1fr_auto] sm:items-center sm:px-8 lg:px-10">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-sand-100 text-slate-700">
                    <svg viewBox="0 0 20 20" class="size-4" fill="none" aria-hidden="true"><path d="M10 6.3v4.1m0 3.3h.01M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-slate-900">From community reports to verified service work.</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">CivicTrack connects resident reports with department triage, field assignments, and recorded outcomes.</p>
                </div>
            </div>
            <a href="{{ route('privacy.notice') }}" class="text-sm font-semibold text-forest-800 underline decoration-forest-200 underline-offset-4 transition hover:text-forest-950">How account information is handled</a>
        </div>
    </section>

    <section id="how-it-works" class="mx-auto max-w-7xl px-5 py-20 sm:px-8 lg:px-10 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">A thoughtful service model</p>
            <h2 class="mt-4 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">Built around follow-through, not just forms.</h2>
            <p class="mt-4 text-base leading-7 text-slate-600">Good civic service depends on clear responsibilities, useful updates, and work that can be verified. CivicTrack brings those parts into one coordinated process.</p>
        </div>

        <div class="mt-11 grid gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-line bg-white p-6 shadow-sm shadow-forest-950/[0.025]">
                <span class="flex size-11 items-center justify-center rounded-xl bg-forest-50 text-forest-800">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M4 5.5h16v11H9l-5 3v-14Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8 9h8m-8 4h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <h3 class="mt-5 text-base font-semibold text-slate-900">A clear place to start</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">Residents can report a local issue, add a location and supporting files, and follow service updates.</p>
            </article>
            <article class="rounded-2xl border border-line bg-white p-6 shadow-sm shadow-forest-950/[0.025]">
                <span class="flex size-11 items-center justify-center rounded-xl bg-forest-50 text-forest-800">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M4 12h5l2.2-5 3.3 10 2.2-5H20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <h3 class="mt-5 text-base font-semibold text-slate-900">Work people can coordinate</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">Department staff and field workers have distinct responsibilities, with assignments and decisions designed to remain accountable.</p>
            </article>
            <article class="rounded-2xl border border-line bg-white p-6 shadow-sm shadow-forest-950/[0.025]">
                <span class="flex size-11 items-center justify-center rounded-xl bg-forest-50 text-forest-800">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M4 19h16M6 15l4-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 7h3v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <h3 class="mt-5 text-base font-semibold text-slate-900">Progress with a record</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">The platform is being built with status history, appropriate privacy, and verified completion as core service principles.</p>
            </article>
        </div>
    </section>

    <section class="bg-sand-100">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-5 py-12 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-10">
            <div>
                <p class="text-sm font-semibold text-forest-900">A secure account is the first step.</p>
                <p class="mt-1 text-sm text-slate-600">Create an account to keep reports together and receive a private report history.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if ($citizenRegistrationOpen)
                    <a href="{{ route('register') }}" class="rounded-lg bg-forest-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-800">Create a citizen account</a>
                @endif
                <a href="{{ route('login') }}" class="rounded-lg border border-forest-900/15 bg-white px-5 py-3 text-sm font-semibold text-forest-900 transition hover:bg-forest-50">Sign in</a>
            </div>
        </div>
    </section>
@endsection
