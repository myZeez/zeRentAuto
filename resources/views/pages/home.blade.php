@extends('layouts.app')
@section('content')
<div class="flex flex-col w-full">
<section class="home-hero w-full max-w-[1360px] mx-auto px-margin-mobile md:px-margin">
<div class="hero-layout">
<div class="hero-copy">
<a class="hero-brand" href="{{ route('home') }}" aria-label="zeAUTOrent — Beranda">
    @include('partials.brand')
</a>
<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-accent-sage-subtle text-accent-sage">
<span class="w-2 h-2 rounded-full bg-accent-sage animate-pulse"></span>
<span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold">Premium Car Rental Indonesia</span>
</div>
<h1 class="hero-title font-display-hero text-on-surface">
          Perjalanan Istimewa Dimulai dari Mobil yang Tepat.
        </h1>
<p class="hero-description font-body-lg text-on-surface-variant">
          Sewa armada mobil premium, SUV eksekutif, dan sports car dengan layanan supir berpengalaman atau lepas kunci. Bersih, terawat, dan selalu tepat waktu di kota Anda.
        </p>
<div class="hero-actions flex flex-wrap items-center gap-space-sm sm:gap-space-md pt-space-xs">
<a class="inline-flex items-center justify-center bg-primary text-on-primary hover:bg-inverse-surface px-space-lg py-3.5 rounded-xl font-label-lg text-label-lg transition-all shadow-md group" href="#quick-search">
<span>Jelajahi Mobil</span>
<span class="material-symbols-outlined ml-2 text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</span>
</a>
<a class="inline-flex items-center justify-center bg-subtle-gray hover:bg-surface-container-highest text-on-surface px-space-lg py-3.5 rounded-xl font-label-lg text-label-lg transition-colors" href="#cara-sewa">
<span>Cara Sewa</span>
</a>
</div>
<a href="{{ route('booking-status') }}" class="inline-flex items-center gap-2 text-sm text-accent-sage"><span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>Sudah booking? Cek status &amp; riwayat →</a>
<div class="hero-metrics grid grid-cols-3 gap-space-md w-full max-w-md">
<div class="flex flex-col">
<span class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight">{{ $cars->sum(fn ($car) => $car['inventory']['total']) }}</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">Unit Kendaraan</span>
</div>
<div class="flex flex-col">
<span class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight">99.8%</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">On-Time Arrival</span>
</div>
<div class="flex flex-col">
<span class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight">24/7</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">Concierge Siaga</span>
</div>
</div>
</div>
<div class="hero-stage">
<img alt="Mercedes-Benz AMG GT silver tampak depan dan samping" class="hero-car" src="{{ asset('images/hero-mercedes.png') }}" width="1698" height="926" fetchpriority="high" decoding="async"/>
<div class="hero-specs">
<div class="hero-spec hero-spec-performance">
<div class="hero-spec-icon bg-subtle-gray">
<span class="material-symbols-outlined text-on-surface">speed</span>
</div>
<div class="flex flex-col">
<span class="hero-spec-label text-on-surface-variant">Performa Murni</span>
<span class="hero-spec-value text-on-surface">435 HP • V6 Biturbo</span>
</div>
</div>
<div class="hero-spec hero-spec-care">
<div class="hero-spec-icon bg-accent-sage-subtle">
<span class="material-symbols-outlined text-accent-sage" style="font-variation-settings: 'FILL' 1;">verified</span>
</div>
<div class="flex flex-col">
<span class="hero-spec-label text-accent-sage">Higienis &amp; Terawat</span>
<span class="hero-spec-value text-on-surface">Sterilisasi Standar 100%</span>
</div>
</div>
</div>
</div>
</div>
</section>
<section class="hero-search w-full max-w-[1360px] mx-auto px-margin-mobile md:px-margin mb-space-2xl" id="quick-search">
<div class="bg-surface rounded-2xl p-space-md sm:p-space-lg shadow-xl shadow-on-surface/5">
<div class="flex flex-wrap items-center justify-between gap-3 mb-space-md">
<div class="flex items-center gap-space-xs">
<button class="px-4 py-2 rounded-full bg-primary text-on-primary font-label-md text-label-md transition-colors" data-service="driver" aria-pressed="true" type="button">Dengan Supir
          </button>
<button class="px-4 py-2 rounded-full bg-subtle-gray hover:bg-surface-container text-on-surface font-label-md text-label-md transition-colors" data-service="self" aria-pressed="false" type="button">Lepas Kunci
          </button>
</div>
<div class="hidden sm:flex items-center gap-2 text-on-surface-variant font-label-sm text-label-sm">
<span class="material-symbols-outlined text-[16px] text-accent-sage" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span>Gratis Pengantaran Bandara Soekarno-Hatta &amp; Ngurah Rai</span>
</div>
</div>
<form class="hero-search-form grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-space-sm sm:gap-space-md" action="{{ route('catalog') }}" method="get" id="quick-search-form">
<!-- Field 1: Pickup Location -->
<div class="flex flex-col space-y-1.5 bg-subtle-gray/70 hover:bg-subtle-gray p-3 rounded-xl transition-colors">
<label class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">location_on</span>
            Kota Penjemputan
          </label>
<select name="city" aria-label="Kota penjemputan" class="bg-transparent font-headline-sm text-headline-sm text-on-surface font-medium focus:outline-none cursor-pointer">
<option value="jakarta">DKI Jakarta &amp; Sekitar</option>
<option value="bali">Bali (Kuta, Seminyak, Denpasar)</option>
<option value="surabaya">Surabaya &amp; Bandara Juanda</option>
<option value="bandung">Bandung &amp; Dago</option>
</select>
</div>
<!-- Field 2: Start Date & Time -->
<div class="flex flex-col space-y-1.5 bg-subtle-gray/70 hover:bg-subtle-gray p-3 rounded-xl transition-colors">
<label class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">calendar_today</span>
            Tanggal Mulai
          </label>
<div class="flex items-center justify-between">
<input class="bg-transparent font-label-lg text-label-lg text-on-surface font-medium focus:outline-none w-full cursor-pointer" name="start_date" aria-label="Tanggal mulai" required type="date" min="{{ today()->toDateString() }}" value="{{ today()->addDay()->toDateString() }}"/>
</div>
</div>
<!-- Field 3: Return Date & Time -->
<div class="flex flex-col space-y-1.5 bg-subtle-gray/70 hover:bg-subtle-gray p-3 rounded-xl transition-colors">
<label class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">event_repeat</span>
            Tanggal Selesai
          </label>
<div class="flex items-center justify-between">
<input class="bg-transparent font-label-lg text-label-lg text-on-surface font-medium focus:outline-none w-full cursor-pointer" name="end_date" aria-label="Tanggal selesai" required type="date" min="{{ today()->addDay()->toDateString() }}" value="{{ today()->addDays(4)->toDateString() }}"/>
</div>
</div>
<!-- Field 4: Vehicle Category -->
<div class="flex flex-col space-y-1.5 bg-subtle-gray/70 hover:bg-subtle-gray p-3 rounded-xl transition-colors">
<label class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">directions_car</span>
            Kategori Kendaraan
          </label>
<select name="category" aria-label="Kategori kendaraan" class="bg-transparent font-headline-sm text-headline-sm text-on-surface font-medium focus:outline-none cursor-pointer">
<option value="all">Semua Tipe Mobil</option>
<option value="sedan">Executive Sedan</option>
<option value="suv">Luxury SUV</option>
<option value="mpv">Premium MPV (Alphard)</option>
<option value="sports">High-End Sports Car</option>
</select>
</div>
<!-- Field 5: Action Button -->
<div class="flex items-end">
<button class="w-full h-[58px] bg-primary hover:bg-inverse-surface text-on-primary font-headline-sm text-headline-sm font-semibold rounded-xl flex items-center justify-center gap-2 transition-colors shadow-md" type="submit">
<span class="material-symbols-outlined text-[20px]">search</span>
<span>Cari Mobil</span>
</button>
</div>
<input type="hidden" name="service" id="quick-service" value="driver"></form>
</div>
</section>
<!-- BRAND PARTNER STRIP -->
<section class="w-full max-w-[1360px] mx-auto px-margin-mobile md:px-margin py-space-md mb-space-2xl">
<div class="brand-ribbon" aria-label="Koleksi merek terpercaya">
<div class="brand-ribbon-label"><span class="brand-ribbon-dot" aria-hidden="true"></span><span>Koleksi Merek<br>Terpercaya</span></div>
<div class="brand-marquee">
<div class="brand-track">
@for($copy = 0; $copy < 2; $copy++)
<ul class="brand-group" @if($copy === 1) aria-hidden="true" @endif>
@foreach(['MERCEDES-BENZ', 'BMW', 'PORSCHE', 'TOYOTA ALPHARD', 'RANGE ROVER', 'HYUNDAI IONIQ'] as $brand)
<li>{{ $brand }}<span aria-hidden="true">✦</span></li>
@endforeach
</ul>
@endfor
</div>
</div>
<button type="button" class="brand-pause" aria-label="Jeda gerakan merek" aria-pressed="false"><span class="material-symbols-outlined" aria-hidden="true">pause</span></button>
</div>
</section>
<!-- POPULAR FLEET SECTION -->
<section class="w-full max-w-[1360px] mx-auto px-margin-mobile md:px-margin mb-space-2xl">
<div class="flex flex-col sm:flex-row sm:items-end justify-between mb-space-lg gap-space-sm">
<div class="space-y-space-xs">
<div class="inline-flex items-center gap-2 text-accent-sage font-label-md text-label-md">
<span class="material-symbols-outlined text-[16px]">stars</span>
<span>Pilihan Teratas Pekan Ini</span>
</div>
<h2 class="font-headline-xl text-headline-xl text-on-surface font-semibold tracking-tight">
          Armada Premium Siap Jalan
        </h2>
<p class="font-body-md text-body-md text-on-surface-variant">
          Dikelola secara mandiri dengan verifikasi 50 poin inspeksi keselamatan setiap perjalanan.
        </p>
</div>
<a class="inline-flex items-center gap-1.5 font-label-lg text-label-lg text-on-surface font-semibold hover:text-accent-sage transition-colors" href="{{ route('catalog') }}">
<span>Lihat Semua Koleksi</span>
<span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</a>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">@foreach(($cars->where('is_featured', true)->isNotEmpty() ? $cars->where('is_featured', true) : $cars)->take(4) as $car) @include('partials.car-card') @endforeach</div></section>
<!-- EDITORIAL SHOWCASE BANNER -->
<section class="w-full max-w-[1360px] mx-auto px-margin-mobile md:px-margin mb-space-2xl">
<div class="bg-surface rounded-3xl p-space-lg sm:p-space-2xl relative overflow-hidden shadow-sm">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-center relative z-10">
<div class="lg:col-span-7 space-y-space-md">
<span class="font-label-sm text-label-sm uppercase tracking-wider text-accent-sage font-bold">
            Standar Kenyamanan Tanpa Kompromi
          </span>
<h2 class="font-headline-xl text-headline-xl text-on-surface font-semibold tracking-tight leading-tight">
            Kenyamanan Eksklusif untuk Mobilitas Bisnis &amp; Liburan Keluarga.
          </h2>
<p class="font-body-md text-body-md text-on-surface-variant max-w-xl">
            Setiap unit zeAUTOrent telah dilengkapi asuransi all-risk komprehensif, air mineral dingin, tisu sanitasi, dan kabel pengisian daya multi-tipe. Layanan pengantaran langsung ke lobi hotel maupun pintu kedatangan terminal VIP bandara.
          </p>
<div class="flex flex-wrap items-center gap-space-md pt-space-xs">
<div class="flex items-center gap-2">
<div class="w-6 h-6 rounded-full bg-accent-sage-subtle flex items-center justify-center">
<span class="material-symbols-outlined text-[16px] text-accent-sage">shield</span>
</div>
<span class="font-label-lg text-label-lg text-on-surface">Asuransi Komprehensif</span>
</div>
<div class="flex items-center gap-2">
<div class="w-6 h-6 rounded-full bg-accent-sage-subtle flex items-center justify-center">
<span class="material-symbols-outlined text-[16px] text-accent-sage">person</span>
</div>
<span class="font-label-lg text-label-lg text-on-surface">Chauffeur Berbahasa Inggris</span>
</div>
<div class="flex items-center gap-2">
<div class="w-6 h-6 rounded-full bg-accent-sage-subtle flex items-center justify-center">
<span class="material-symbols-outlined text-[16px] text-accent-sage">schedule</span>
</div>
<span class="font-label-lg text-label-lg text-on-surface">Jaminan Tepat Waktu</span>
</div>
</div>
</div>
<div class="lg:col-span-5 relative">
<div class="aspect-[4/3] rounded-2xl overflow-hidden bg-subtle-gray shadow-md">
<img alt="Interior Mewah zeAUTOrent" class="w-full h-full object-cover" data-alt="Elegant luxury black SUV interior with plush leather seats, panoramic glass roof, warm sunlight softly filtering through windows, business executive working with laptop in rear seats." src="https://lh3.googleusercontent.com/aida-public/AB6AXuD3gJ-Qwx7ag9fKQ0FUkdKpSzTZDoA_fdo1BemnxyLKIWKIA4QaMTLhgbepNfcffom0maMhVhsCDr_7KEnHIK9xiifDqXM2Irlocb6L3r71-jJiuaEeqvZqd9Su9v5SMiQIQ_9SqADj8oHK4iHycUk8yal9oSy6J9Q8xsigeLhgVA0yeb5ni0jAe8HIPR-3SR_-Vv_P4u_vHhww_JlBQC9WMmP8s-UfXfHzpiZ7fHJKL6HMMF5HbhOnJw"/>
</div>
</div>
</div>
</div>
</section>
<!-- KEUNGGULAN LAYANAN (4 BENEFIT CARDS) -->
<section class="w-full max-w-[1360px] mx-auto px-margin-mobile md:px-margin mb-space-2xl">
<div class="text-center max-w-2xl mx-auto mb-space-xl">
<span class="font-label-sm text-label-sm uppercase tracking-wider text-accent-sage font-bold">Mengapa Memilih zeAUTOrent</span>
<h2 class="font-headline-xl text-headline-xl text-on-surface font-semibold tracking-tight mt-1">
        Pengalaman Sewa Tanpa Rasa Khawatir
      </h2>
<p class="font-body-md text-body-md text-on-surface-variant mt-2">
        Komitmen profesionalisme kami hadir di setiap detail, dari saat Anda melakukan reservasi hingga mobil dikembalikan.
      </p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-md">
<!-- Feature 1 -->
<div class="bg-surface rounded-2xl p-space-lg shadow-sm hover:shadow-md transition-shadow">
<div class="w-12 h-12 rounded-xl bg-subtle-gray flex items-center justify-center text-on-surface mb-space-md">
<span class="material-symbols-outlined text-[24px]">verified</span>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold mb-1.5">Armada Kondisi Prima</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">
          Unit mobil berusia di bawah 3 tahun dengan riwayat servis berkala resmi dan pembersihan detail sebelum serah terima.
        </p>
</div>
<!-- Feature 2 -->
<div class="bg-surface rounded-2xl p-space-lg shadow-sm hover:shadow-md transition-shadow">
<div class="w-12 h-12 rounded-xl bg-subtle-gray flex items-center justify-center text-on-surface mb-space-md">
<span class="material-symbols-outlined text-[24px]">receipt_long</span>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold mb-1.5">Harga Transparan</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">
          Nol biaya tersembunyi. Semua tarif yang Anda lihat mencakup proteksi dasar, pajak legalitas, dan administrasi lengkap.
        </p>
</div>
<!-- Feature 3 -->
<div class="bg-surface rounded-2xl p-space-lg shadow-sm hover:shadow-md transition-shadow">
<div class="w-12 h-12 rounded-xl bg-subtle-gray flex items-center justify-center text-on-surface mb-space-md">
<span class="material-symbols-outlined text-[24px]">flight_land</span>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold mb-1.5">Pengantaran Fleksibel</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">
          Antar-jemput unit langsung di terminal bandara internasional, lobi hotel, kantor bisnis, atau kediaman pribadi Anda.
        </p>
</div>
<!-- Feature 4 -->
<div class="bg-surface rounded-2xl p-space-lg shadow-sm hover:shadow-md transition-shadow">
<div class="w-12 h-12 rounded-xl bg-subtle-gray flex items-center justify-center text-on-surface mb-space-md">
<span class="material-symbols-outlined text-[24px]">support_agent</span>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold mb-1.5">Roadside Support 24/7</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">
          Tim bantuan teknis darurat siap bertindak kapan pun dibutuhkan untuk memastikan perjalanan Anda tetap lancar terlindungi.
        </p>
</div>
</div>
</section>
<!-- CARA SEWA (4 HORIZONTAL STEPS) -->
<section class="w-full max-w-[1360px] mx-auto px-margin-mobile md:px-margin mb-space-2xl" id="cara-sewa">
<div class="bg-surface rounded-3xl p-space-lg sm:p-space-xl shadow-sm">
<div class="flex flex-col sm:flex-row sm:items-end justify-between mb-space-xl gap-space-sm">
<div>
<span class="font-label-sm text-label-sm uppercase tracking-wider text-accent-sage font-bold">Kemudahan Operasional</span>
<h2 class="font-headline-xl text-headline-xl text-on-surface font-semibold tracking-tight mt-1">
            4 Langkah Mudah Memulai Sewa
          </h2>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant max-w-sm">
          Reservasi mobil impian Anda tuntas hanya dalam hitungan 3 menit tanpa antrean berbelit.
        </p>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-lg relative">
<!-- Step 1 -->
<div class="flex flex-col space-y-2">
<div class="flex items-center gap-3">
<span class="w-10 h-10 rounded-full bg-primary text-on-primary flex items-center justify-center font-headline-sm text-headline-sm font-bold">1</span>
<div class="h-0.5 flex-1 bg-subtle-gray"></div>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold pt-2">Pilih Mobil</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">
            Pilih kendaraan impian dari katalog sedan, SUV, maupun van eksekutif sesuai preferensi gaya Anda.
          </p>
</div>
<!-- Step 2 -->
<div class="flex flex-col space-y-2">
<div class="flex items-center gap-3">
<span class="w-10 h-10 rounded-full bg-subtle-gray text-on-surface flex items-center justify-center font-headline-sm text-headline-sm font-bold">2</span>
<div class="h-0.5 flex-1 bg-subtle-gray"></div>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold pt-2">Tentukan Jadwal</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">
            Atur tanggal, durasi rental harian/mingguan, serta lokasi serah terima unit di kota pilihan Anda.
          </p>
</div>
<!-- Step 3 -->
<div class="flex flex-col space-y-2">
<div class="flex items-center gap-3">
<span class="w-10 h-10 rounded-full bg-subtle-gray text-on-surface flex items-center justify-center font-headline-sm text-headline-sm font-bold">3</span>
<div class="h-0.5 flex-1 bg-subtle-gray"></div>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold pt-2">Konfirmasi Pesanan</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">
            Lengkapi verifikasi identitas secara aman dan lakukan pembayaran dengan metode favorit Anda.
          </p>
</div>
<!-- Step 4 -->
<div class="flex flex-col space-y-2">
<div class="flex items-center gap-3">
<span class="w-10 h-10 rounded-full bg-accent-sage-subtle text-accent-sage flex items-center justify-center font-headline-sm text-headline-sm font-bold">4</span>
<div class="h-0.5 flex-1 bg-accent-sage/30"></div>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold pt-2">Mobil Siap Digunakan</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">
            Kendaraan diantar dalam kondisi bersih sempurna dan siap menemani perjalanan prestisius Anda.
          </p>
</div>
</div>
</div>
</section>
<!-- TESTIMONI PELANGGAN (3 Editorial Cards) -->
<section class="w-full max-w-[1360px] mx-auto px-margin-mobile md:px-margin mb-space-2xl">
<div class="flex flex-col sm:flex-row sm:items-end justify-between mb-space-lg gap-space-sm">
<div>
<span class="font-label-sm text-label-sm uppercase tracking-wider text-accent-sage font-bold">Testimoni Nyata</span>
<h2 class="font-headline-xl text-headline-xl text-on-surface font-semibold tracking-tight mt-1">
          Dipercaya oleh Ribuan Profesional
        </h2>
</div>
<div class="flex items-center gap-2">
<span class="font-headline-lg text-headline-lg font-bold text-on-surface">4.9 / 5</span>
<div class="flex text-accent-sage">
<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
<!-- Testimonial 1 -->
<div class="bg-surface rounded-2xl p-space-lg shadow-sm flex flex-col justify-between">
<div class="space-y-space-sm">
<span class="font-display-hero text-headline-xl text-on-surface-variant/40 font-serif leading-none">“</span>
<p class="font-body-md text-body-md text-on-surface">
            Layanan sewa Mercedes C300 di Bali sangat memuaskan. Mobil diantar ke bandara tepat waktu, interior sangat bersih tanpa bau rokok, dan proses check-out hanya memakan waktu 3 menit.
          </p>
</div>
<div class="flex items-center gap-3 pt-space-md mt-space-md bg-transparent">
<div class="w-11 h-11 rounded-full bg-subtle-gray overflow-hidden">
<img alt="Arya Wicaksono" class="w-full h-full object-cover" data-alt="Portrait of an Indonesian professional business traveler wearing a smart casual blazer, warm friendly expression against a clean office backdrop." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBZiRp7KmfB2Plh0-uevxymGCGsQKs3psyPogGfyw5MIQTEr7zfxE3MEG0PUTR-JIba2-RYxvzWXICiXQ4A45e7MkF63OgsQp7GkAfYxcBUeXJfccj6meEtw4thEHOTczusD1RPd5XW-qLgMTHGQ9wElnK4WG0rAd2uSY5_kUKj59kYNMc432wJJz6S0FJOiLrasDOvDRjTXTiPrdiunmUcFmRZz9pv6eCgTm2NRCBRso_1DJIkMVUcUQ"/>
</div>
<div>
<h4 class="font-label-lg text-label-lg text-on-surface font-semibold">Arya Wicaksono</h4>
<span class="font-body-sm text-body-sm text-on-surface-variant">Managing Director, Jakarta</span>
</div>
</div>
</div>
<!-- Testimonial 2 -->
<div class="bg-surface rounded-2xl p-space-lg shadow-sm flex flex-col justify-between">
<div class="space-y-space-sm">
<span class="font-display-hero text-headline-xl text-on-surface-variant/40 font-serif leading-none">“</span>
<p class="font-body-md text-body-md text-on-surface">
            Sewa Alphard untuk rombongan delegasi bisnis dari Singapura di Surabaya. Supir sangat santun, paham rute cepat, dan bahasa Inggrisnya lancar. zeAUTOrent selalu menjadi pilihan utama perusahaan kami.
          </p>
</div>
<div class="flex items-center gap-3 pt-space-md mt-space-md bg-transparent">
<div class="w-11 h-11 rounded-full bg-subtle-gray overflow-hidden">
<img alt="Stephanie Santoso" class="w-full h-full object-cover" data-alt="Close up portrait of a smiling female corporate executive in smart formal attire with neutral modern minimalist architectural background." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAHu2fWMpqwkT02gsVhKJGZqmxhmPQMiaJd_A-fmRknpb2BV7mNt0XxavCBWL7rSVmLAcWtJ5gJESFsc-XhCFsik1jx3d_Bp9YGDbt2rGycJpxJGEuzrJOEvKYiLZ4UkvDbg2r4Ra5SYlSSbvzEf2metPqcfnfIyYKguZsdrmweE0Rd-_vJX27g1PlMdYLEkf5ln5FKY5E4p1uKaBk5HvwF9EMmh3aRwEwovzZSxkVoECqlhcua0ruFLA"/>
</div>
<div>
<h4 class="font-label-lg text-label-lg text-on-surface font-semibold">Stephanie Santoso</h4>
<span class="font-body-sm text-body-sm text-on-surface-variant">Corporate Secretary, Surabaya</span>
</div>
</div>
</div>
<!-- Testimonial 3 -->
<div class="bg-surface rounded-2xl p-space-lg shadow-sm flex flex-col justify-between">
<div class="space-y-space-sm">
<span class="font-display-hero text-headline-xl text-on-surface-variant/40 font-serif leading-none">“</span>
<p class="font-body-md text-body-md text-on-surface">
            Mobil Porsche Macan dalam kondisi sangat terawat seperti baru keluar dari showroom. Transparansi deposit dan pengembalian dana sangat cepat. Standar bintang lima yang sesungguhnya!
          </p>
</div>
<div class="flex items-center gap-3 pt-space-md mt-space-md bg-transparent">
<div class="w-11 h-11 rounded-full bg-subtle-gray overflow-hidden">
<img alt="Dimas Pratama" class="w-full h-full object-cover" data-alt="Portrait of an energetic tech entrepreneur in his early 30s with natural lighting, soft focus background." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBs3niQbSrtJCQd01y1-fRhDoHO5ErE4dzYA0s9wdDJJS8Zp_zdoYwGdZFU8-S2wmadbR06QXl0LilA7cU2F5w6gd8lLuiV3VA5PAoAt0nc9nqUdUTVwk6W4k4BYQw4Gz6SM2s3r0DVQ6GgT2qd0U18lYakOvxOMJQ1-TT64slneOoFXeXlQq1CwKwa2VdL_HRPS6BQnwhtj_VDsFjdviqD15W1h0nB1SgPbbdoWu0y3uvFoBvMg7-Jsg"/>
</div>
<div>
<h4 class="font-label-lg text-label-lg text-on-surface font-semibold">Dimas Pratama</h4>
<span class="font-body-sm text-body-sm text-on-surface-variant">Tech Founder, Bandung</span>
</div>
</div>
</div>
</div>
</section>
<!-- FAQ ACCORDION -->
<section class="w-full max-w-[1360px] mx-auto px-margin-mobile md:px-margin mb-space-2xl">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-space-xl">
<div class="lg:col-span-4 space-y-space-xs">
<span class="font-label-sm text-label-sm uppercase tracking-wider text-accent-sage font-bold">Informasi Penting</span>
<h2 class="font-headline-xl text-headline-xl text-on-surface font-semibold tracking-tight">
          Pertanyaan yang Sering Diajukan
        </h2>
<p class="font-body-md text-body-md text-on-surface-variant pt-2">
          Punya pertanyaan spesifik tentang persyaratan, asuransi, atau kustomisasi rute? Tim kami siap menjawab 24/7.
        </p>
<div class="pt-space-md">
<a class="inline-flex items-center gap-2 px-space-md py-2.5 rounded-xl bg-subtle-gray hover:bg-surface-container font-label-lg text-label-lg text-on-surface transition-colors" href="{{ route('info', 'hubungi-kami') }}">
<span class="material-symbols-outlined text-[18px]">chat</span>
<span>Hubungi Concierge WhatsApp</span>
</a>
</div>
</div>
<div class="lg:col-span-8 space-y-space-sm" id="faq-container">
<!-- Item 1 -->
<div class="bg-surface rounded-2xl p-space-md shadow-sm transition-all">
<button class="w-full flex items-center justify-between text-left font-headline-sm text-headline-sm text-on-surface font-semibold faq-toggle" type="button" aria-expanded="false">
<span>Apa saja dokumen yang dibutuhkan untuk sewa lepas kunci?</span>
<span class="material-symbols-outlined text-on-surface-variant transition-transform faq-icon">expand_more</span>
</button>
<div class="faq-content mt-space-sm font-body-sm text-body-sm text-on-surface-variant">
            Untuk WNI, dokumen mencakup KTP asli, SIM A aktif, bukti tiket penerbangan/reservasi hotel, serta deposit jaminan. Untuk WNA, diperlukan Paspor, International Driving Permit (IDP), serta bukti akomodasi resmi.
          </div>
</div>
<!-- Item 2 -->
<div class="bg-surface rounded-2xl p-space-md shadow-sm transition-all">
<button class="w-full flex items-center justify-between text-left font-headline-sm text-headline-sm text-on-surface font-semibold faq-toggle" type="button" aria-expanded="false">
<span>Apakah tarif sewa sudah termasuk bahan bakar dan tol?</span>
<span class="material-symbols-outlined text-on-surface-variant transition-transform faq-icon">expand_more</span>
</button>
<div class="faq-content hidden mt-space-sm font-body-sm text-body-sm text-on-surface-variant">
            Tarif dasar mencakup mobil, supir (untuk paket With Chauffeur), dan asuransi komprehensif. BBM, e-toll, dan biaya parkir dibayarkan secara terpisah sesuai pemakaian riil perjalanan, atau Anda dapat memilih paket All-Inclusive kami.
          </div>
</div>
<!-- Item 3 -->
<div class="bg-surface rounded-2xl p-space-md shadow-sm transition-all">
<button class="w-full flex items-center justify-between text-left font-headline-sm text-headline-sm text-on-surface font-semibold faq-toggle" type="button" aria-expanded="false">
<span>Bagaimana prosedur pengantaran mobil ke bandara?</span>
<span class="material-symbols-outlined text-on-surface-variant transition-transform faq-icon">expand_more</span>
</button>
<div class="faq-content hidden mt-space-sm font-body-sm text-body-sm text-on-surface-variant">
            Staf representatif zeAUTOrent akan menyambut Anda langsung di pintu keluar terminal kedatangan dengan name-board, melakukan inspeksi digital ringkas bersama Anda, dan menyerahkan kunci mobil dalam kurun waktu 5 menit.
          </div>
</div>
<!-- Item 4 -->
<div class="bg-surface rounded-2xl p-space-md shadow-sm transition-all">
<button class="w-full flex items-center justify-between text-left font-headline-sm text-headline-sm text-on-surface font-semibold faq-toggle" type="button" aria-expanded="false">
<span>Bagaimana jika terjadi kerusakan atau kendala teknis di jalan?</span>
<span class="material-symbols-outlined text-on-surface-variant transition-transform faq-icon">expand_more</span>
</button>
<div class="faq-content hidden mt-space-sm font-body-sm text-body-sm text-on-surface-variant">
            Seluruh kendaraan terproteksi penuh oleh asuransi All-Risk. Jika terjadi kendala mekanis, tim patroli derek darurat zeAUTOrent Roadside Assistance 24 Jam akan segera datang dan menyediakan mobil pengganti yang setara tanpa biaya tambahan.
          </div>
</div>
</div>
</div>
</section>
<!-- BOTTOM CALL TO ACTION -->
<section class="w-full max-w-[1360px] mx-auto px-margin-mobile md:px-margin pb-space-2xl">
<div class="bg-primary text-on-primary rounded-3xl p-space-xl sm:p-space-2xl relative overflow-hidden flex flex-col items-center text-center">
<!-- Subtle visual accent rings -->
<div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-inverse-surface/40 pointer-events-none"></div>
<div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-accent-sage/10 pointer-events-none"></div>
<div class="relative z-10 max-w-2xl space-y-space-md">
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-inverse-surface text-accent-sage font-label-sm text-label-sm uppercase tracking-wider font-semibold">
          Reservasi Cepat &amp; Fleksibel
        </span>
<h2 class="font-headline-xl text-headline-xl text-on-primary font-bold tracking-tight">
          Siap Merasakan Pengalaman Berkendara Kelas Atas?
        </h2>
<p class="font-body-md text-body-md text-primary-fixed-dim">
          Hubungi concierge kami atau telusuri katalog lengkap untuk memastikan ketersediaan armada eksklusif bagi jadwal Anda berikutnya.
        </p>
<div class="flex flex-wrap items-center justify-center gap-space-md pt-space-xs">
<a class="bg-surface text-primary hover:bg-surface-container font-headline-sm text-headline-sm px-space-xl py-3.5 rounded-xl transition-all font-semibold shadow-lg" href="#quick-search">
            Mulai Reservasi Sekarang
          </a>
<a class="inline-flex items-center gap-2 bg-transparent hover:bg-inverse-surface text-on-primary font-headline-sm text-headline-sm px-space-lg py-3.5 rounded-xl transition-colors" href="{{ route('info', 'hubungi-kami') }}">
<span class="material-symbols-outlined text-[20px]">call</span>
<span>+62 21 5088 9900</span>
</a>
</div>
</div>
</div>
</section>
<!-- Interactive script for FAQ accordions -->

</div>
@endsection
