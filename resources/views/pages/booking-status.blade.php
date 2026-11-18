@extends('layouts.app')
@section('title', 'Status & Riwayat — zeAUTOrent')
@section('skip-welcome', 'true')
@push('head')<meta name="robots" content="noindex, nofollow">@endpush
@section('content')
<div class="rental-status-page">
    <div class="status-topbar"><a href="{{ route('home') }}" aria-label="zeAUTOrent — Beranda">@include('partials.brand')</a><a class="status-back" href="{{ route('home') }}"><span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>Beranda</a></div>
    <div class="status-intro {{ $hasAccess ? 'status-intro-open' : '' }}">
        <div><span class="status-eyebrow">RUANG PERJALANAN ANDA</span><h1>Status &amp;<br>Riwayat.</h1><p>Semua perjalanan, dalam satu tempat. Pantau proses booking dan waktu pengembalian mobil Anda.</p><div class="status-intro-note"><span class="material-symbols-outlined" aria-hidden="true">schedule</span><span>Jadwal jelas.<br>Perjalanan lebih tenang.</span></div></div>
        @if(!$access)
        <form method="post" action="{{ route('booking-status.lookup') }}" class="status-lookup">@csrf
            <span class="status-form-icon material-symbols-outlined" aria-hidden="true">travel_explore</span><h2>Temukan perjalanan Anda</h2><p>Gunakan nomor telepon saat booking dan kode unik reservasi yang ingin Anda lihat. Hanya booking yang cocok dengan keduanya akan ditampilkan.</p>
            <label for="lookup-phone">Nomor telepon<input id="lookup-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required maxlength="20" placeholder="Contoh: 0812 3456 7890" value="{{ old('phone') }}"></label>
            <label for="lookup-code">Kode booking<input id="lookup-code" name="code" type="text" autocomplete="off" autocapitalize="characters" spellcheck="false" required maxlength="30" placeholder="ATR-XXXXXXXXXXXX" value="{{ old('code') }}"></label>
            <button type="submit">Lihat status &amp; riwayat<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></button>
            <small><span class="material-symbols-outlined" aria-hidden="true">lock</span>Kode booking tercantum pada halaman konfirmasi. Akses untuk booking ini berlaku selama 30 menit.</small>
        </form>
        @else
        <div class="status-access"><span class="status-form-icon material-symbols-outlined" aria-hidden="true">verified_user</span><h2>Booking Anda terbuka</h2><p>Kode <strong>{{ $access['code'] }}</strong> · nomor telepon <strong>•••• {{ $access['last_digits'] }}</strong>.</p><p>Hanya reservasi ini yang ditampilkan. Perbarui halaman untuk melihat perubahan status dari admin.</p><form method="post" action="{{ route('booking-status.close') }}">@csrf<button class="status-close" type="submit">Tutup / cari booking lain<span class="material-symbols-outlined" aria-hidden="true">logout</span></button></form></div>
        @endif
    </div>
    @if($hasAccess)
    <div class="status-stats"><div><span>{{ $access ? 'Booking ditemukan' : 'Semua booking' }}</span><strong>{{ $stats['total'] }}</strong></div><div><span>Sedang disewa</span><strong>{{ $stats['active'] }}</strong></div><div><span>Perjalanan selesai</span><strong>{{ $stats['completed'] }}</strong></div></div>
    <div class="status-list-heading"><div><span class="status-eyebrow">DARI BOOKING HINGGA PULANG</span><h2>Perjalanan Anda</h2></div><span>{{ $bookings->total() }} booking</span></div>
    <div class="status-bookings">
    @forelse($bookings as $booking)
        @php
            $car = $cars->get($booking->reservation['car_id']);
            $step = ['demo' => 0, 'confirmed' => 1, 'active' => 2, 'completed' => 3][$booking->status] ?? -1;
            $late = $booking->isOverdue();
            $statusLabel = $late ? 'Terlambat dikembalikan' : (\App\Models\Booking::STATUSES[$booking->status] ?? $booking->status);
        @endphp
        <article class="status-booking">
            <div class="status-booking-top"><span class="status-booking-code">{{ $booking->code }}</span><span class="status-badge {{ $late ? 'status-badge-late' : 'status-badge-'.$booking->status }}" @if($booking->status === 'active') data-rental-badge="{{ $booking->id }}" @endif>{{ $statusLabel }}</span></div>
            <div class="status-car">@if($car)<img src="{{ $car->image }}" alt="{{ $car->name }}" loading="lazy">@endif<div><span class="status-eyebrow">{{ $booking->reservation['service'] === 'driver' ? 'DENGAN SUPIR' : 'LEPAS KUNCI' }}</span><h3>{{ $car?->name ?? 'Kendaraan reservasi' }}</h3><p>{{ $booking->quote['days'] }} hari · {{ $booking->reservation['location'] }}</p></div></div>
            @if($booking->status !== 'cancelled')
            @include('partials.selected-unit', ['reservation' => $booking->reservation, 'showTimes' => false])
            <ol class="status-timeline" aria-label="Progres booking">@foreach(['Booking diterima', 'Dikonfirmasi', 'Sedang disewa', 'Selesai'] as $index => $label)<li class="{{ $index <= $step ? 'is-reached' : '' }} {{ $index === $step ? 'is-current' : '' }}" @if($index === $step) aria-current="step" @endif><span class="status-step">@if($index < $step)<span class="material-symbols-outlined" aria-hidden="true">check</span>@else{{ $index + 1 }}@endif</span><span>{{ $label }}</span></li>@endforeach</ol>
            @else<p class="status-cancel-note">Booking ini telah dibatalkan. Tidak ada penyewaan yang sedang berlangsung.</p>@endif
            <div class="status-schedule"><div><span>Mulai sewa</span><strong>{{ $booking->starts_at?->translatedFormat('d M Y') ?? \Carbon\Carbon::parse($booking->reservation['start_date'])->translatedFormat('d M Y') }}</strong><small>{{ $booking->starts_at ? $booking->starts_at->format('H:i').' WIB' : 'Jam menunggu konfirmasi' }}</small></div><div class="status-schedule-due"><span>Batas pengembalian</span><strong>{{ $booking->due_at?->translatedFormat('d M Y') ?? \Carbon\Carbon::parse($booking->reservation['end_date'])->translatedFormat('d M Y') }}</strong><small>{{ $booking->due_at ? $booking->due_at->format('H:i').' WIB' : 'Jam menunggu konfirmasi' }}</small></div></div>
            @if($booking->status === 'active' && $booking->due_at)
            <div class="status-deadline {{ $late ? 'is-late' : '' }}" data-rental-deadline="{{ $booking->due_at->timestamp * 1000 }}" data-booking-id="{{ $booking->id }}"><span class="material-symbols-outlined" aria-hidden="true">{{ $late ? 'warning' : 'timer' }}</span><div><strong data-deadline-label>{{ $late ? 'Batas pengembalian terlewati' : 'Sewa sedang berjalan' }}</strong><p data-deadline-text>{{ $late ? 'Segera koordinasikan pengembalian kendaraan.' : 'Kembalikan mobil paling lambat '.$booking->due_at->translatedFormat('d M Y, H:i').' WIB.' }}</p></div></div>
            @elseif($booking->status === 'completed')
            <div class="status-returned"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><div><strong>Kendaraan sudah dikembalikan</strong><p>@if($booking->returned_at){{ $booking->returned_at->translatedFormat('d M Y, H:i') }} WIB · {{ $booking->returnedLate() ? 'Dikembalikan melewati batas waktu' : 'Dikembalikan tepat waktu' }}@else Waktu pengembalian belum dicatat.@endif</p></div></div>
            @elseif(!$booking->due_at && $booking->status !== 'cancelled')
            <p class="status-pending-note">Admin akan mengonfirmasi jam mulai dan batas pengembalian sebelum sewa berjalan.</p>
            @endif
            <details class="status-booking-details"><summary>Rincian booking<span class="material-symbols-outlined" aria-hidden="true">expand_more</span></summary><dl><div><dt>Dibuat</dt><dd>{{ $booking->created_at->translatedFormat('d M Y, H:i') }} WIB</dd></div><div><dt>Total reservasi</dt><dd>Rp {{ number_format($booking->quote['total'], 0, ',', '.') }}</dd></div><div><dt>Catatan Anda</dt><dd>{{ $booking->customer['notes'] ?? 'Tidak ada catatan.' }}</dd></div></dl></details>
        </article>
    @empty
        <div class="status-empty"><span class="material-symbols-outlined" aria-hidden="true">receipt_long</span><h2>Belum ada perjalanan</h2><p>Riwayat booking Anda akan tampil di sini.</p><a href="{{ route('catalog') }}">Jelajahi mobil →</a></div>
    @endforelse
    </div>
    <div class="status-pagination">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection
