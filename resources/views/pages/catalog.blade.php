@extends('layouts.app')
@section('title', 'Katalog Mobil — zeAUTOrent')
@section('content')
<div class="max-w-[1360px] mx-auto px-4 md:px-8 py-10">
    <nav class="text-body-sm text-ink-secondary mb-4"><a href="{{ route('home') }}">Beranda</a> / Cari Mobil</nav>
    <div class="flex flex-wrap justify-between items-end gap-4 mb-8"><div><h1 class="font-headline-xl text-headline-xl">Katalog Armada Premium</h1><p class="text-ink-secondary mt-2">Pilihan kendaraan istimewa untuk setiap perjalanan Anda.</p></div><span class="rounded-full bg-accent-sage-subtle text-accent-sage px-4 py-2 text-label-md">{{ $cars->count() }} Mobil Ditemukan</span></div>
    <div class="grid lg:grid-cols-12 gap-8 items-start">
        <aside class="lg:col-span-3 bg-surface rounded-2xl shadow-sm p-6 lg:sticky lg:top-24">
            <div class="flex justify-between mb-6"><h2 class="font-headline-md text-headline-md">Filter Mobil</h2><a href="{{ route('catalog') }}" class="text-body-sm text-ink-secondary underline">Reset</a></div>
            <form action="{{ route('catalog') }}" method="get" id="catalog-filter" class="space-y-5">
                <div><label for="q" class="block text-label-md mb-2">Cari nama mobil</label><input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Mercedes, Porsche..." class="w-full bg-canvas-offwhite border border-border-subtle rounded-xl p-3"></div>
                <div><label for="city" class="block text-label-md mb-2">Lokasi Penjemputan</label><select id="city" name="city" class="w-full bg-canvas-offwhite rounded-xl p-3">@foreach(['jakarta'=>'DKI Jakarta','bali'=>'Bali','surabaya'=>'Surabaya','bandung'=>'Bandung'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['city'] ?? 'jakarta') === $value)>{{ $label }}</option>@endforeach</select></div>
                <fieldset><legend class="text-label-md mb-3">Kategori Kendaraan</legend>@foreach(['all'=>'Semua Tipe Mobil','sedan'=>'Executive Sedan','suv'=>'Luxury SUV','mpv'=>'Premium MPV','sports'=>'Sports Car'] as $value=>$label)<label class="flex gap-3 items-center py-2"><input type="radio" name="category" value="{{ $value }}" @checked(($filters['category'] ?? 'all') === $value) class="accent-black"> {{ $label }}</label>@endforeach</fieldset>
                <div><label for="max-price" class="block text-label-md mb-2">Harga maksimal per hari</label><input id="max-price" name="max_price" type="range" min="1000000" max="5000000" step="50000" value="{{ $filters['max_price'] ?? 5000000 }}" class="w-full accent-black"><output id="price-output" for="max-price" class="block font-semibold mt-1">Rp {{ number_format($filters['max_price'] ?? 5000000, 0, ',', '.') }}</output></div>
                <div class="grid grid-cols-2 gap-2"><label class="text-label-sm">Mulai<input type="date" name="start_date" min="{{ today()->toDateString() }}" value="{{ $filters['start_date'] ?? '' }}" class="w-full mt-2 bg-canvas-offwhite p-2 rounded-lg"></label><label class="text-label-sm">Selesai<input type="date" name="end_date" min="{{ today()->addDay()->toDateString() }}" value="{{ $filters['end_date'] ?? '' }}" class="w-full mt-2 bg-canvas-offwhite p-2 rounded-lg"></label></div>
                <input type="hidden" name="service" value="{{ $filters['service'] ?? 'self' }}">
                <button type="submit" class="w-full bg-primary text-white rounded-xl py-3 font-semibold">Terapkan Filter</button>
            </form>
            <p class="text-xs text-ink-secondary mt-5">Armada dan harga adalah data demo. Saat ini tersedia untuk wilayah Jakarta.</p>
        </aside>
        <section class="lg:col-span-9">
            <div class="flex flex-wrap justify-between gap-3 mb-6 bg-surface p-4 rounded-xl"><label class="text-body-sm flex gap-2 items-center">Urutkan <select name="sort" form="catalog-filter" id="sortSelector" class="font-semibold bg-transparent">@foreach(['populer'=>'Paling Populer','termurah'=>'Harga: Termurah','termahal'=>'Harga: Termahal','terbaru'=>'Tahun Terbaru'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['sort'] ?? 'populer') === $value)>{{ $label }}</option>@endforeach</select></label><div><button id="viewGridBtn" aria-label="Tampilan grid" aria-pressed="true" class="p-2 rounded-lg bg-subtle-gray"><span class="material-symbols-outlined">grid_view</span></button><button id="viewListBtn" aria-label="Tampilan daftar" aria-pressed="false" class="p-2 rounded-lg"><span class="material-symbols-outlined">view_list</span></button></div></div>
            <div id="carCatalogGrid" class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">@forelse($cars as $car) @include('partials.car-card') @empty<div class="col-span-full bg-surface rounded-2xl text-center py-16 px-6"><span class="material-symbols-outlined text-5xl text-ink-secondary">search_off</span><h2 class="font-headline-md text-headline-md mt-4">Mobil belum ditemukan</h2><p class="text-ink-secondary mt-2">Coba kategori, lokasi, atau rentang harga lainnya.</p><a href="{{ route('catalog') }}" class="inline-block mt-5 underline">Reset filter</a></div>@endforelse</div>
        </section>
    </div>
</div>
@endsection

