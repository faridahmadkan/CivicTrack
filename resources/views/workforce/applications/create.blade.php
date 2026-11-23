@extends('layouts.'.($isGuestApplicant ? 'auth' : 'citizen'))

@section('title', 'Apply for '.($type === App\Enums\WorkforceApplicationType::Staff ? 'staff access' : 'field worker access'))

@section('content')
    <div class="max-w-4xl">
        @unless ($isGuestApplicant)
            <x-back-link href="{{ route('workforce.applications.index') }}" class="mb-6">Workforce applications</x-back-link>
        @endunless
        <p class="mt-3 text-xs font-bold uppercase tracking-[0.18em] text-forest-700">{{ $type === App\Enums\WorkforceApplicationType::Staff ? 'Staff coordinator application' : 'Field worker application' }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">{{ $isGuestApplicant ? 'Create an account and apply' : 'Apply for '.($type === App\Enums\WorkforceApplicationType::Staff ? 'staff coordination' : 'field worker').' access' }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">An administrator reviews each application. You can sign in and use your citizen workspace while it is pending; operational access begins only after approval.</p>
    </div>

    @if (! $registrationOpen)
        <section class="mt-7 max-w-4xl rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-white p-5 shadow-sm" role="status">
            <h2 class="text-sm font-semibold text-amber-950">Applications are currently closed</h2>
            <p class="mt-1 text-sm leading-6 text-amber-900">The operating organization has closed this application path. Your account information has not been submitted.</p>
            <x-back-link href="{{ route('public.home') }}" class="mt-4">Return to CivicTrack</x-back-link>
        </section>
    @elseif ($organizationUnits->isEmpty())
        <section class="mt-7 max-w-4xl rounded-2xl border border-line bg-white p-5 shadow-sm" role="status">
            <h2 class="text-sm font-semibold text-slate-900">No service units are available</h2>
            <p class="mt-1 text-sm leading-6 text-slate-600">The organization has not configured an active unit for applications yet. Please return later.</p>
        </section>
    @elseif ($isGuestApplicant)
        <form method="POST" action="{{ route('workforce.applications.register', $type->value) }}" enctype="multipart/form-data" class="mt-7 max-w-4xl space-y-5">
            @csrf
            <x-form-messages />
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7" aria-labelledby="create-account-heading">
                <div class="mb-5 flex items-start gap-3 border-b border-line pb-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-forest-50 text-sm font-bold text-forest-800">1</span>
                    <div><h2 id="create-account-heading" class="text-base font-semibold text-forest-950">Your account</h2><p class="mt-1 text-sm text-slate-600">This citizen account lets you sign in and follow the application review.</p></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label for="name" class="block text-sm font-semibold text-slate-800">Full name
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" maxlength="120" class="mt-1.5 w-full rounded-xl border border-line px-3.5 py-3 text-sm font-normal shadow-sm focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100" placeholder="Your full name">
                        @error('name')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
                    </label>
                    <label for="email" class="block text-sm font-semibold text-slate-800">Email address
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" inputmode="email" maxlength="255" class="mt-1.5 w-full rounded-xl border border-line px-3.5 py-3 text-sm font-normal shadow-sm focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100" placeholder="you@example.com">
                        @error('email')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
                    </label>
                    <label for="password" class="block text-sm font-semibold text-slate-800">Create password
                        <input id="password" name="password" type="password" required autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-line px-3.5 py-3 text-sm font-normal shadow-sm focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100">
                        <span class="mt-1 block text-xs font-normal text-slate-500">Use a strong password that you do not use elsewhere.</span>
                        @error('password')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
                    </label>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-800">Confirm password
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-line px-3.5 py-3 text-sm font-normal shadow-sm focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100">
                    </label>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-3 rounded-xl border border-forest-100 bg-forest-50/70 p-4 text-xs leading-5 text-forest-950 sm:grid-cols-3">
                    <span class="flex items-center gap-2"><svg viewBox="0 0 16 16" class="size-4 shrink-0" fill="none" aria-hidden="true"><path d="m3 8.5 3.2 3L13 4.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg>Citizen access starts now</span>
                    <span class="flex items-center gap-2"><svg viewBox="0 0 16 16" class="size-4 shrink-0" fill="none" aria-hidden="true"><path d="m3 8.5 3.2 3L13 4.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg>Workforce tools stay locked</span>
                    <span class="flex items-center gap-2"><svg viewBox="0 0 16 16" class="size-4 shrink-0" fill="none" aria-hidden="true"><path d="m3 8.5 3.2 3L13 4.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg>Admin approves applications</span>
                </div>
                @if ($requireEmailVerification)
                    <p class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-3 text-xs leading-5 text-blue-950">Email verification is enabled. We’ll send a verification link after you submit; confirm your email to open your citizen workspace.</p>
                @else
                    <p class="mt-4 rounded-xl border border-forest-200 bg-forest-50 p-3 text-xs leading-5 text-forest-950">Email verification is disabled by the site administrator. You can open your citizen workspace immediately after submission.</p>
                @endif
            </section>

            <x-citizen-profile-fields :map-center="$mapCenter" id-prefix="workforce-signup" />

            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7" aria-labelledby="application-details-heading">
                <div class="mb-5 flex items-start gap-3 border-b border-line pb-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-forest-50 text-sm font-bold text-forest-800">2</span>
                    <div><h2 id="application-details-heading" class="text-base font-semibold text-forest-950">Application details</h2><p class="mt-1 text-sm text-slate-600">Choose the service unit and share experience that helps administrators review your application.</p></div>
                </div>
                @include('workforce.applications._application-fields')
            </section>

            <label class="flex items-start gap-3 rounded-2xl border border-line bg-white p-4 text-sm leading-6 text-slate-600 shadow-sm">
                <input name="privacy_acknowledged" type="checkbox" value="1" @checked(old('privacy_acknowledged')) required class="mt-1 size-4 shrink-0 rounded border-slate-300 text-forest-800 focus:ring-forest-700">
                <span>I have read the <a href="{{ route('privacy.notice') }}" target="_blank" rel="noopener" class="font-semibold text-forest-800 underline decoration-forest-200 underline-offset-4">privacy notice</a> and understand how account and application information is used.</span>
            </label>
            @error('privacy_acknowledged')<span class="-mt-3 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror

            <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-center text-sm text-slate-600">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-forest-800 underline underline-offset-4">Sign in</a></p>
                <button type="submit" class="group inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-forest-900 px-5 py-3 text-sm font-semibold text-white shadow-md shadow-forest-950/10 transition duration-200 hover:-translate-y-0.5 hover:bg-forest-800 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Create account and submit application <svg viewBox="0 0 20 20" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" aria-hidden="true"><path d="M4.5 10h11m0 0-4.5-4.5m4.5 4.5-4.5 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
            </div>
        </form>
    @else
        <form method="POST" action="{{ route('workforce.applications.store', $type->value) }}" enctype="multipart/form-data" class="mt-7 max-w-4xl rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-8">
            @csrf
            <x-form-messages />
            @include('workforce.applications._application-fields')
            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between"><x-back-link href="{{ route('workforce.applications.index') }}">Cancel</x-back-link><button type="submit" class="group inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-forest-900 px-5 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-forest-800">Submit application <svg viewBox="0 0 20 20" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" aria-hidden="true"><path d="M4.5 10h11m0 0-4.5-4.5m4.5 4.5-4.5 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg></button></div>
        </form>
    @endif
@endsection
