@extends('layouts.public')

@section('title', 'Report submitted')

@section('content')
    <section class="mx-auto max-w-3xl px-5 py-14 sm:px-8 sm:py-20">
    <x-back-link href="{{ route('public.home') }}" class="mb-5">CivicTrack home</x-back-link>
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm sm:p-9">
            <span class="inline-flex size-11 items-center justify-center rounded-full bg-forest-50 text-forest-800" aria-hidden="true">✓</span>
            <p class="mt-6 text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Report received</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Keep your tracking details safe.</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">This report was submitted as a guest. The reference alone cannot open it. Save both the reference and the private access code below.</p>

            <dl class="mt-7 grid gap-4 rounded-2xl bg-sand-50 p-5 sm:grid-cols-2">
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Report reference</dt><dd class="mt-2 font-mono text-sm font-semibold text-forest-950">{{ $report->public_reference }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Private access code</dt><dd class="mt-2 break-all font-mono text-sm font-semibold text-forest-950">{{ $guestAccessToken }}</dd></div>
            </dl>
            <p class="mt-4 text-xs leading-5 text-slate-500">The code is shown once here and stored only as a secure hash. CivicTrack cannot retrieve it later.</p>
            <form method="POST" action="{{ route('public.reports.verify-access', $report) }}" class="mt-7">
                @csrf
                <input type="hidden" name="token" value="{{ $guestAccessToken }}">
                <button class="w-full rounded-lg bg-forest-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-forest-800 sm:w-auto">Open report tracking</button>
            </form>
            <a href="{{ route('public.home') }}" class="mt-5 inline-block text-sm font-semibold text-slate-600 hover:text-forest-900">Return to CivicTrack</a>
        </div>
    </section>
@endsection
