@extends('layouts.admin')
@section('title', 'Reservasi')
@section('content')
<header class="admin-page-header"><div><p class="admin-eyebrow">PERJALANAN PELANGGAN</p><h1>Reservasi</h1><p>{{ $bookings->total() }} reservasi sesuai pencarian Anda.</p></div></header>
<form method="get" class="admin-filter admin-form"><label class="admin-search"><span class="material-symbols-outlined" aria-hidden="true">search</span><input name="q" aria-label="Cari kode reservasi" value="{{ $filters['q'] ?? '' }}" placeholder="Cari kode reservasi…" maxlength="100"></label><select name="status" aria-label="Status reservasi"><option value="all">Semua status</option>@foreach(\App\Models\Booking::STATUSES as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>@endforeach</select><button class="admin-button admin-button-secondary" type="submit">Terapkan</button></form>
<section class="admin-panel admin-booking-list">@forelse($bookings as $booking) @include('admin.booking-row') @empty<div class="admin-empty"><span class="material-symbols-outlined" aria-hidden="true">event_note</span><h2>Belum ada hasil</h2><p>Reservasi masuk akan tampil di sini. Coba ubah pencarian atau status.</p></div>@endforelse</section><div class="admin-pagination">{{ $bookings->links() }}</div>
@endsection
