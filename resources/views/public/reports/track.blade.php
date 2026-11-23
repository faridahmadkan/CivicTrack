@extends('layouts.public')

@section('title', 'Report tracking')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-10 sm:px-8 sm:py-14">
        <x-back-link href="{{ route('public.home') }}">CivicTrack home</x-back-link>
        <div class="mt-7">@include('reports._details', ['guestAccess' => true])</div>
    </section>
@endsection
