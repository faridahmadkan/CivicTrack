@extends('layouts.citizen')

@section('title', 'Report '.$report->public_reference)

@section('content')
    <div class="mx-auto max-w-6xl">
        <x-back-link href="{{ route('citizen.reports.index') }}" class="mb-6">My reports</x-back-link>
        @include('reports._details', ['guestAccess' => false])
    </div>
@endsection
