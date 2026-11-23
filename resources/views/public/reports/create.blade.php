@extends('layouts.public')

@section('title', 'Report a civic issue')

@section('content')
    <section class="mx-auto max-w-4xl px-5 py-12 sm:px-8 sm:py-16">
        <a href="{{ route('public.home') }}" class="text-sm font-semibold text-forest-800 hover:text-forest-950">← CivicTrack home</a>
        <p class="mt-8 text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Resident report</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">Tell us what needs attention.</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Share the service issue and where it is happening. You can submit as a guest or sign in to keep your reports together.</p>

        @if ($categories->isEmpty())
            <div class="mt-8 rounded-2xl border border-sand-100 bg-white p-6 text-sm leading-6 text-slate-600">The civic service catalog is not available yet. Please try again later.</div>
        @else
            <div class="mt-8 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7">
                @include('reports._form', ['action' => route('public.reports.store')])
            </div>
        @endif
    </section>
@endsection
