@extends('layouts.public')
@section('content')
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <h1 class="fw-bold text-navy">Contact CivicTrack</h1>
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            <form method="POST" action="{{ route('contact') }}" class="card border-0 shadow-sm p-4">
                @csrf
                <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                <div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="5" required></textarea></div>
                <button class="btn btn-emerald">Send Message</button>
            </form>
        </div>
    </div>
</main>
@endsection
