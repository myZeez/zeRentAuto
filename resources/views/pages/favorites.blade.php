@extends('layouts.app')
@section('title', 'Favorit — zeAUTOrent')
@section('content')
<section class="max-w-[1360px] mx-auto px-4 md:px-8 py-12" id="favorites-page"><h1 class="font-headline-xl text-headline-xl">Mobil Favorit Anda</h1><p class="mt-2 mb-8 text-ink-secondary">Pilihan tersimpan di browser ini.</p><p id="favorites-empty" class="hidden bg-surface p-8 rounded-xl">Belum ada mobil favorit. <a href="{{ route('catalog') }}" class="underline text-accent-sage">Jelajahi katalog</a>.</p><div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">@foreach($cars as $car) @include('partials.car-card') @endforeach</div></section>
@endsection
