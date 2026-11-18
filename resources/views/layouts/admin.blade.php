<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — zeAUTOrent</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="admin-body">
@php($navigation = [['admin.dashboard', 'space_dashboard', 'Ringkasan'], ['admin.cars', 'directions_car', 'Armada'], ['admin.bookings', 'receipt_long', 'Reservasi'], ['admin.account', 'person', 'Akun']])
<aside class="admin-sidebar">
    <a href="{{ route('admin.dashboard') }}" class="admin-brand" aria-label="Ringkasan admin zeAUTOrent">@include('partials.brand')</a>
    <p class="admin-eyebrow admin-nav-label">RUANG PENGELOLAAN</p>
    <nav aria-label="Navigasi admin" class="admin-side-nav">
        @foreach($navigation as [$route, $icon, $label])
        <a href="{{ route($route) }}" @if(request()->routeIs($route, $route.'.*') || ($route === 'admin.cars' && request()->routeIs('admin.units*')) || ($route === 'admin.bookings' && request()->routeIs('admin.booking*'))) aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">{{ $icon }}</span>{{ $label }}</a>
        @endforeach
    </nav>
    <div class="admin-sidebar-bottom"><a href="{{ route('home') }}" class="admin-site-link"><span class="material-symbols-outlined" aria-hidden="true">open_in_new</span>Buka situs</a><div class="admin-user"><span class="admin-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><div><strong>{{ auth()->user()->name }}</strong><span>Administrator</span></div></div><form method="post" action="{{ route('logout') }}">@csrf<button class="admin-logout" type="submit"><span class="material-symbols-outlined" aria-hidden="true">logout</span>Keluar akun</button></form></div>
</aside>
<header class="admin-mobile-header"><a href="{{ route('admin.dashboard') }}" aria-label="Ringkasan admin zeAUTOrent">@include('partials.brand')</a><div><span class="admin-admin-tag">ADMIN</span><a href="{{ route('admin.account') }}" class="admin-avatar" aria-label="Pengaturan akun">{{ mb_substr(auth()->user()->name, 0, 1) }}</a></div></header>
<main class="admin-main">
    @if(session('status'))<div role="status" class="admin-notice"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span>{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="admin-error"><strong>Periksa kembali isian Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<nav aria-label="Navigasi admin ponsel" class="admin-bottom-nav">
    @foreach($navigation as [$route, $icon, $label])
    <a href="{{ route($route) }}" @if(request()->routeIs($route, $route.'.*') || ($route === 'admin.cars' && request()->routeIs('admin.units*')) || ($route === 'admin.bookings' && request()->routeIs('admin.booking*'))) aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">{{ $icon }}</span><span>{{ $label }}</span></a>
    @endforeach
</nav>
</body></html>
