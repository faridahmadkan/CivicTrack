@extends('layouts.auth')

@section('title', 'Reset password')

@section('content')
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Account recovery</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">Reset your password</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Enter your account email. If it is registered, CivicTrack will send a secure password reset link.</p>
    </div>
    <div class="mt-6"><x-form-messages /></div>
    <form method="POST" action="{{ route('password.email') }}" class="mt-1 space-y-5">
        @csrf
        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-800">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm outline-none transition focus:border-forest-700 focus:ring-4 focus:ring-forest-100" placeholder="you@example.com">
        </div>
        <button type="submit" class="w-full rounded-lg bg-forest-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-800">Send password reset link</button>
    </form>
    <p class="mt-7 text-center text-sm"><a href="{{ route('login') }}" class="font-semibold text-forest-800 underline decoration-forest-200 underline-offset-4 hover:text-forest-950">Return to sign in</a></p>
@endsection
