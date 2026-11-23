@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Welcome back</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">Sign in to CivicTrack</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Use your registered email and password to access your account.</p>
    </div>

    <div class="mt-7"><x-form-messages /></div>

    <form method="POST" action="{{ route('login') }}" class="mt-1 space-y-5">
        @csrf
        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-800">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" inputmode="email" class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-forest-700 focus:ring-4 focus:ring-forest-100" placeholder="you@example.com" aria-describedby="email-help">
            <p id="email-help" class="mt-1.5 text-xs text-slate-500">Enter the email address associated with your account.</p>
        </div>
        <div>
            <div class="mb-2 flex items-center justify-between gap-3">
                <label for="password" class="text-sm font-semibold text-slate-800">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-forest-800 underline decoration-forest-200 underline-offset-4 hover:text-forest-950">Forgot password?</a>
            </div>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
        </div>
        <label class="flex items-start gap-2.5 text-sm text-slate-600">
            <input name="remember" type="checkbox" value="1" class="mt-0.5 size-4 rounded border-slate-300 text-forest-800 focus:ring-forest-700">
            <span>Keep me signed in on this device</span>
        </label>
        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-forest-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-800">Sign in securely</button>
    </form>

    <p class="mt-7 text-center text-sm text-slate-600">New to CivicTrack? <a href="{{ route('register') }}" class="font-semibold text-forest-800 underline decoration-forest-200 underline-offset-4 hover:text-forest-950">Create a citizen account</a></p>
@endsection
