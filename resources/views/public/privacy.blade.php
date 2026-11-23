@extends('layouts.public')

@section('title', 'Privacy notice')

@section('content')
    <section class="mx-auto max-w-4xl px-5 py-14 sm:px-8 sm:py-20 lg:px-10">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Privacy and account security</p>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-forest-950">Privacy notice</h1>
        <p class="mt-4 text-sm text-slate-500">Account notice version {{ config('civictrack.privacy_notice_version') }}</p>

        <div class="mt-10 space-y-8 rounded-2xl border border-line bg-white p-6 shadow-sm sm:p-9">
            <section>
                <h2 class="text-lg font-semibold text-slate-900">What this notice covers</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">CivicTrack is an independent civic technology platform, not an official government website. This notice describes the information collected to create and secure a citizen account in the current release. Reporting and workforce functions are being introduced separately.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold text-slate-900">Account information</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">When you register, CivicTrack stores your name, email address, password hash, account status, email verification time, and the time and version of the privacy notice you acknowledged. You may optionally provide a phone number. Passwords are stored as one-way hashes and are never written to the audit record.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold text-slate-900">Security and accountability</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">The platform records account registration and selected security events with a timestamp, account reference, and limited request metadata such as IP address and browser user agent. These records support security and accountability. Access is restricted to authorized platform operations.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold text-slate-900">How information is used</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">Account information supports sign-in, email verification, password recovery, and account administration. CivicTrack does not currently send your information to a map, advertising, or analytics provider as part of account registration.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold text-slate-900">Retention and contact</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">Retention periods, local privacy rights, and an accountable service contact must be completed by the organization operating this installation before public launch. Account information should be retained only as long as needed for the published service and applicable records policy.</p>
            </section>
        </div>

        <p class="mt-7 text-xs leading-5 text-slate-500">The organization deploying CivicTrack must review and complete this notice for its jurisdiction and operating policy before accepting public registrations.</p>
    </section>
@endsection
