@extends('layouts.auth')

@section('title', 'Choose a new password')

@section('content')
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Account recovery</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">Choose a new password</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Set a strong password for your CivicTrack account.</p>
    </div>
    <div class="mt-6"><x-form-messages /></div>
    <form method="POST" action="{{ route('password.update') }}" class="mt-1 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-800">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autocomplete="email" class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm outline-none transition focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
        </div>
        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-800">New password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm outline-none transition focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
        </div>
        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-800">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm outline-none transition focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
        </div>
        <button type="submit" class="w-full rounded-lg bg-forest-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-800">Update password</button>
    </form>
@endsection
