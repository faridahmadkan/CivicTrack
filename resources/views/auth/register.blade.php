@extends('layouts.auth')

@section('title', 'Create a citizen account')

@section('content')
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Resident access</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">Create your account</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Use an email address you can access. @if (config('civictrack.require_email_verification')) We’ll ask you to verify it before opening your account workspace.@else Your account can open immediately without email verification.@endif</p>
    </div>

    <div class="mt-6"><x-form-messages /></div>

    @if (! $registrationOpen)
        <div class="mt-1 rounded-xl border border-amber-200 bg-amber-50 p-5" role="status">
            <h2 class="text-sm font-semibold text-amber-950">Citizen registration is currently closed</h2>
            <p class="mt-2 text-sm leading-6 text-amber-900">New accounts are not being accepted at this time. Please return later or sign in to an existing account.</p>
            <a href="{{ route('login') }}" class="mt-4 inline-flex rounded-lg bg-amber-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-900">Go to sign in</a>
        </div>
    @else
        <form method="POST" action="{{ route('register') }}" class="mt-1 space-y-4">
            @csrf
            <div>
                <label for="name" class="mb-2 block text-sm font-semibold text-slate-800">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" maxlength="120" class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-forest-700 focus:ring-4 focus:ring-forest-100" placeholder="Your name">
                @error('name')<p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-800">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" inputmode="email" class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-forest-700 focus:ring-4 focus:ring-forest-100" placeholder="you@example.com">
                @error('email')<p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p>@enderror
            </div>
            <x-citizen-profile-fields :map-center="$mapCenter" id-prefix="citizen-signup" />
            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-800">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
                <p class="mt-1.5 text-xs text-slate-500">Use a strong password that you do not use elsewhere.</p>
                @error('password')<p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-800">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
            </div>
            <label class="flex items-start gap-3 rounded-xl border border-line bg-white p-3.5 text-sm leading-6 text-slate-600">
                <input name="privacy_acknowledged" type="checkbox" value="1" @checked(old('privacy_acknowledged')) required class="mt-1 size-4 shrink-0 rounded border-slate-300 text-forest-800 focus:ring-forest-700">
                <span>I have read the <a href="{{ route('privacy.notice') }}" target="_blank" rel="noopener" class="font-semibold text-forest-800 underline decoration-forest-200 underline-offset-4">privacy notice</a> and understand how account information is used.</span>
            </label>
            @error('privacy_acknowledged')<p class="-mt-3 text-xs font-medium text-red-700">{{ $message }}</p>@enderror
            @error('registration')<p class="text-xs font-medium text-red-700">{{ $message }}</p>@enderror
            <button type="submit" class="group inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-forest-900 px-5 py-3 text-sm font-semibold text-white shadow-md shadow-forest-950/10 transition duration-200 hover:-translate-y-0.5 hover:bg-forest-800 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Create citizen account <svg viewBox="0 0 20 20" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" aria-hidden="true"><path d="M4.5 10h11m0 0-4.5-4.5m4.5 4.5-4.5 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-600">Already registered? <a href="{{ route('login') }}" class="font-semibold text-forest-800 underline decoration-forest-200 underline-offset-4 hover:text-forest-950">Sign in</a></p>
    @endif
@endsection
