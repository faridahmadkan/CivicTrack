@extends('layouts.auth')

@section('title', 'Verify your email')

@section('content')
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">One more step</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">Verify your email</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">We sent a verification link to your email address. Open it to confirm your account and continue to your secure workspace.</p>
    </div>
    <div class="mt-6"><x-form-messages /></div>
    <form method="POST" action="{{ route('verification.send') }}" class="mt-1">
        @csrf
        <button type="submit" class="w-full rounded-lg bg-forest-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-800">Send a new verification link</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="w-full rounded-lg border border-line bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-sand-100">Sign out</button>
    </form>
@endsection
