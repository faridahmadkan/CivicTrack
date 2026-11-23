@extends('layouts.public')

@section('title', 'Report a civic issue')

@section('content')
    <section class="mx-auto max-w-4xl px-5 py-12 sm:px-8 sm:py-16">
        <x-back-link href="{{ route('public.home') }}">CivicTrack home</x-back-link>
        <p class="mt-8 text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Resident report</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">Tell us what needs attention.</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Share the service issue and where it is happening. You can submit as a guest or sign in to keep your reports together.</p>

        @if ($categories->isEmpty())
            <x-empty-state class="mt-8" title="The service catalog is unavailable" description="There are no active public categories right now. Please try again later." />
        @else
            <div class="mt-8 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-7">
                @include('reports._form', ['action' => route('public.reports.store')])
            </div>
        @endif
    </section>
@endsection
