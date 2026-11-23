@extends('layouts.public')

@section('title', 'Access report tracking')

@section('content')
    <section class="mx-auto max-w-xl px-5 py-14 sm:px-8 sm:py-20">
        <a href="{{ route('public.home') }}" class="mb-5 inline-flex rounded-lg text-sm font-semibold text-forest-800 hover:text-forest-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">← CivicTrack home</a>
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Private report access</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Enter your access code.</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">Report <span class="font-mono font-semibold text-slate-800">{{ $report->public_reference }}</span> is private. Use the code you received when you submitted it.</p>
            <form method="POST" action="{{ route('public.reports.verify-access', $report) }}" class="mt-7 space-y-4">
                @csrf
                <label class="block text-sm font-semibold text-slate-800">64-character access code
                    <input name="token" value="{{ old('token') }}" required minlength="64" maxlength="64" pattern="[a-fA-F0-9]{64}" autocomplete="off" class="mt-2 w-full rounded-xl border border-line px-3.5 py-3 font-mono text-sm font-normal" placeholder="Enter your private code">
                    @error('token')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
                </label>
                <button class="w-full rounded-lg bg-forest-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-forest-800">View report</button>
            </form>
            <p class="mt-5 text-xs leading-5 text-slate-500">If you submitted this report while signed in, <a href="{{ route('login') }}" class="font-semibold text-forest-800 underline underline-offset-2">sign in to your citizen account</a> to view it.</p>
        </div>
    </section>
@endsection
