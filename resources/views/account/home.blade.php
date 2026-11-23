@extends('layouts.auth')

@section('title', 'Your account')

@section('content')
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Your CivicTrack account</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">Welcome, {{ $user->name }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Your email address has been verified and your account is active.</p>
    </div>

    <section class="mt-7 rounded-2xl border border-line bg-white p-5 shadow-sm" aria-labelledby="account-details-heading">
        <h2 id="account-details-heading" class="text-sm font-semibold text-slate-900">Account details</h2>
        <dl class="mt-4 divide-y divide-line text-sm">
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:justify-between sm:gap-6">
                <dt class="text-slate-500">Email</dt>
                <dd class="break-all font-medium text-slate-800">{{ $user->email }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:justify-between sm:gap-6">
                <dt class="text-slate-500">Account access</dt>
                <dd class="flex flex-wrap gap-2">
                    @foreach ($roles as $role)
                        <span class="rounded-full bg-forest-50 px-2.5 py-1 text-xs font-semibold capitalize text-forest-900">{{ str_replace('_', ' ', $role) }}</span>
                    @endforeach
                </dd>
            </div>
        </dl>
    </section>

    @if ($user->hasRole(App\Enums\RoleName::Citizen->value) && ! $user->hasAnyRole([App\Enums\RoleName::Staff->value, App\Enums\RoleName::Worker->value]))
        <a href="{{ route('citizen.reports.index') }}" class="mt-5 flex items-center justify-between gap-4 rounded-xl border border-line bg-white p-4 text-sm transition hover:border-forest-200 hover:bg-forest-50/50">
            <span><span class="block font-semibold text-slate-900">My civic reports</span><span class="mt-1 block text-xs leading-5 text-slate-500">Follow report status and service team updates.</span></span>
            <span class="text-lg text-forest-800" aria-hidden="true">→</span>
        </a>
        <a href="{{ route('workforce.applications.index') }}" class="mt-5 flex items-center justify-between gap-4 rounded-xl border border-line bg-white p-4 text-sm transition hover:border-forest-200 hover:bg-forest-50/50">
            <span><span class="block font-semibold text-slate-900">Workforce applications</span><span class="mt-1 block text-xs leading-5 text-slate-500">View your application history or apply when a path is open.</span></span>
            <span class="text-lg text-forest-800" aria-hidden="true">→</span>
        </a>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button type="submit" class="w-full rounded-lg border border-line bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-sand-100">Sign out</button>
    </form>
@endsection
