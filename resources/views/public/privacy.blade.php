@extends('layouts.public')

@section('title', 'Privacy notice')

@section('content')
    <section class="mx-auto max-w-4xl px-5 py-14 sm:px-8 sm:py-20 lg:px-10">
        <x-back-link href="{{ route('public.home') }}" class="mb-6">CivicTrack home</x-back-link>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Privacy and account security</p>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-forest-950">Privacy notice</h1>
        <p class="mt-4 text-sm text-slate-500">Account notice version {{ config('civictrack.privacy_notice_version') }}</p>

        <div class="mt-10 space-y-8 rounded-2xl border border-line bg-white p-6 shadow-sm sm:p-9">
            <section>
                <h2 class="text-lg font-semibold text-slate-900">What this notice covers</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">CivicTrack is an independent civic technology platform, not an official government website. This notice covers citizen accounts, reports, workforce applications, and public map features. The organization operating this installation must complete the contact, retention, and jurisdiction-specific details before public launch.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold text-slate-900">Account information</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">When you register, CivicTrack stores your name, email address, password hash, account status, email verification time, age, and city, district, or neighborhood. You may optionally provide a phone number and a neighborhood map pin. The map pin is rounded to neighborhood-level precision and is never shown on the public map. Passwords are stored as one-way hashes and are never written to the audit record.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold text-slate-900">Security and accountability</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">The platform records account registration and selected security events with a timestamp, account reference, and limited request metadata such as IP address and browser user agent. These records support security and accountability. Access is restricted to authorized platform operations.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold text-slate-900">How information is used</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">Account information supports sign-in, email verification, password recovery, and account administration. Report details, precise locations, and private uploads are available to the responsible authorized service team. Public map markers use generalized coordinates and show only the approved service category and status; they do not show report titles, references, addresses, contact details, or attachments.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold text-slate-900">Maps and place search</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">The map uses configured street and satellite tile providers. A tile provider receives requests for the map areas visible in your browser, along with normal network information such as your IP address. If you press Search place, the place name or address you entered is sent to {{ config('civictrack.map.place_search.name') }} to find matching public places. The search request does not include the report’s saved coordinate fields. For a report, a result changes its location only after you confirm it. Search is an explicit action and does not run as you type.</p>
                <p class="mt-2 text-sm leading-7 text-slate-600">Place-search and imagery providers have their own privacy, licensing, and usage terms. Map providers can be changed through the deployment’s configuration. Do not enter personal or confidential information into the place search field.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold text-slate-900">Retention and contact</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">Retention periods, local privacy rights, and an accountable service contact must be completed by the organization operating this installation before public launch. Account information should be retained only as long as needed for the published service and applicable records policy.</p>
            </section>
        </div>

        <p class="mt-7 text-xs leading-5 text-slate-500">The organization deploying CivicTrack must review and complete this notice for its jurisdiction and operating policy before accepting public registrations.</p>
    </section>
@endsection
