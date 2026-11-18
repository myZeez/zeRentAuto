@extends('layouts.app')
@section('title', $title.' — zeAUTOrent')
@section('content')
<section class="max-w-3xl mx-auto px-4 py-16"><a href="{{ route('home') }}" class="text-accent-sage">← Beranda</a><div class="bg-surface rounded-2xl p-8 mt-6 shadow-sm"><h1 class="font-headline-xl text-headline-xl">{{ $title }}</h1><p class="text-body-lg text-ink-secondary mt-6">{{ $description }}</p><a href="{{ route('catalog') }}" class="inline-block bg-primary text-white px-6 py-3 rounded-xl mt-8">Jelajahi Mobil</a></div></section>
@endsection
