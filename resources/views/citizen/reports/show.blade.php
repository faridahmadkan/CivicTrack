@extends('layouts.citizen')

@section('title', 'Report '.$report->public_reference)

@section('content')
    <div class="mx-auto max-w-6xl">@include('reports._details', ['guestAccess' => false])</div>
@endsection
