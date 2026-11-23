@extends('layouts.public')

@section('title', 'Report tracking')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-10 sm:px-8 sm:py-14">
        <a href="{{ route('public.home') }}" class="text-sm font-semibold text-forest-800 hover:text-forest-950">← CivicTrack home</a>
        <div class="mt-7">@include('reports._details', ['guestAccess' => true])</div>
    </section>
@endsection
