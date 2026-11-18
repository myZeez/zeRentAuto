@extends('layouts.app')
@section('title', 'Pesanan Saya — zeAUTOrent')
@section('content')
<section class="max-w-5xl mx-auto px-4 py-12"><h1 class="font-headline-xl text-headline-xl mb-2">Pesanan Saya</h1><p class="text-ink-secondary mb-8">Riwayat reservasi demo pada sesi browser ini.</p>
@forelse($bookings as $booking)
    <a href="{{ route('confirmation', $booking->code) }}" class="flex flex-col sm:flex-row gap-6 items-center bg-surface p-6 rounded-2xl shadow-sm mb-4 hover:shadow-md"><img src="{{ $cars[$booking->reservation['car_id']]['image'] }}" alt="{{ $cars[$booking->reservation['car_id']]['name'] }}" class="w-48 h-28 object-contain"><div class="flex-1"><span class="text-label-sm text-accent-sage">{{ $booking->code }} · {{ \App\Models\Booking::STATUSES[$booking->status] ?? $booking->status }}</span><h2 class="font-headline-md text-headline-md">{{ $cars[$booking->reservation['car_id']]['name'] }}</h2><p class="text-ink-secondary">{{ $booking->reservation['start_date'] }} → {{ $booking->reservation['end_date'] }}</p></div><span class="font-semibold">Rp {{ number_format($booking->quote['total'], 0, ',', '.') }} →</span></a>
@empty
    <div class="rounded-2xl bg-surface p-12 text-center"><span class="material-symbols-outlined text-5xl text-accent-sage">receipt_long</span><h2 class="font-headline-md text-headline-md mt-4">Belum ada pesanan</h2><p class="text-ink-secondary mt-2">Temukan mobil untuk perjalanan Anda berikutnya.</p><a href="{{ route('catalog') }}" class="inline-block mt-6 bg-primary text-white px-6 py-3 rounded-xl">Cari Mobil</a></div>
@endforelse
</section>
@endsection
