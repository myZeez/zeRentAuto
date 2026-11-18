<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/><meta content="web_standard" name="shell-type"/><title>@yield('title', 'zeAUTOrent — Premium Car Rental')</title><link href="https://fonts.googleapis.com" rel="preconnect"/><link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Plus+Jakarta+Sans:wght@600;700&amp;display=swap" rel="stylesheet"/><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('head')</head>
<body class="bg-canvas-offwhite font-body-md text-body-md text-on-surface antialiased">
@unless(View::hasSection('skip-welcome')) @include('partials.welcome') @endunless
<div id="scroll-progress" class="scroll-progress no-print" aria-hidden="true"></div>
<main class="min-h-screen">
@if($errors->any())<div role="alert" class="max-w-[1296px] mx-auto mt-6 p-4 rounded-xl bg-error-container text-on-error-container"><strong>Periksa kembali formulir Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main>
@include('partials.footer')
<div id="toast" role="status" class="hidden fixed bottom-5 left-1/2 -translate-x-1/2 z-[100] rounded-xl bg-primary text-white px-5 py-3 shadow-xl"></div>
@stack('scripts')
</body></html>
