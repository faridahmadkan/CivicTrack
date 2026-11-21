<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#142b22">
<title>{{ $title ?? 'CivicTrack Workspace' }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head><body>
<div class="d-flex dashboard-shell" id="dashboardShell">
<aside class="dashboard-sidebar">
<a href="{{ route('home') }}" class="dashboard-brand"><span class="brand-mark"><i class="bi bi-shield-check"></i></span>CivicTrack</a>
<div class="sidebar-caption">Workspace</div><nav class="nav flex-column">@yield('sidebar')</nav>
<div class="sidebar-bottom"><div class="fw-semibold text-white mb-1">CivicTrack</div><div>Community reporting workspace</div></div>
</aside>
<main class="dashboard-main">
<nav class="navbar dashboard-topbar">
<div class="d-flex align-items-center gap-2"><button class="btn btn-sm btn-outline-secondary mobile-nav-toggle" type="button" data-sidebar-toggle aria-label="Open navigation" aria-expanded="false"><i class="bi bi-list fs-5"></i></button><span class="small text-secondary d-none d-sm-inline topbar-context">Workspace</span><i class="bi bi-chevron-right small text-secondary d-none d-sm-inline"></i><span class="fw-semibold small">{{ auth()->user()->role === 'admin' ? 'Administration' : (auth()->user()->role === 'staff' ? 'Field operations' : 'Citizen portal') }}</span></div>
<div class="d-flex align-items-center gap-3 ms-auto">
<a class="text-secondary position-relative" href="{{ route('notifications.index') }}" title="Notifications" aria-label="Notifications"><i class="bi bi-bell fs-5"></i>@if(auth()->user()->unreadNotifications->count())<span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle">{{ auth()->user()->unreadNotifications->count() }}</span>@endif</a>
<div class="d-flex align-items-center gap-2"><span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><div class="d-none d-md-block lh-sm"><div class="fw-semibold small">{{ auth()->user()->name }}</div><div class="text-secondary" style="font-size:.72rem;text-transform:capitalize">{{ auth()->user()->role }}</div></div></div>
<a class="text-secondary" href="{{ route('profile.edit') }}" title="Profile settings" aria-label="Profile settings"><i class="bi bi-gear fs-5"></i></a>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">Sign out</button></form>
</div></nav>
<div class="dashboard-content">
@if(session('status'))<div class="alert alert-success mb-4"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger mb-4"><div class="fw-semibold mb-1">Please review the following:</div><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</div></main></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>