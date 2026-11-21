<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'CivicTrack Dashboard' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
<div class="d-flex flex-column flex-lg-row">
    <aside class="dashboard-sidebar p-3">
        <a href="{{ route('home') }}" class="d-flex align-items-center text-white text-decoration-none mb-4"><i class="bi bi-shield-check fs-4 me-2"></i><span class="fw-bold">CivicTrack</span></a>
        <nav class="nav flex-column">
            @yield('sidebar')
        </nav>
    </aside>
    <main class="flex-grow-1">
        <nav class="navbar navbar-light bg-white border-bottom px-3 px-lg-4">
            <span class="navbar-text fw-semibold">{{ auth()->user()->name }}</span>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <a class="text-decoration-none text-secondary" href="{{ route('profile.edit') }}" title="Profile"><i class="bi bi-person-circle fs-5"></i></a>
                <a class="text-decoration-none position-relative text-secondary" href="{{ route('notifications.index') }}" title="Notifications">
                    <i class="bi bi-bell fs-5"></i>
                    @if(auth()->user()->unreadNotifications->count())<span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle">{{ auth()->user()->unreadNotifications->count() }}</span>@endif
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">Logout</button></form>
            </div>
        </nav>
        <div class="p-3 p-lg-4">
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
