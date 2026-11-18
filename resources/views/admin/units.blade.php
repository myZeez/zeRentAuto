@extends('layouts.admin')
@section('title', 'Unit kendaraan')
@section('content')
<a href="{{ route('admin.cars') }}" class="admin-back"><span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>Semua armada</a>
<header class="admin-page-header"><div><p class="admin-eyebrow">UNIT KENDARAAN</p><h1>{{ $car->name }}</h1><p>Kelola setiap kendaraan, warna, dan kesiapan unit.</p></div><a class="admin-button admin-button-secondary" href="{{ route('admin.cars.edit', $car) }}">Edit tipe mobil</a></header>
<div class="admin-unit-count"><span>{{ $units->count() }} unit terdaftar</span><span>{{ $inventory['available'] }} tersedia saat ini</span><span>{{ $inventory['rented'] }} sedang disewa</span></div>
<div class="admin-unit-layout"><div class="admin-unit-list">
@forelse($units as $unit)
@php($stock = collect($inventory['units'])->firstWhere('id', $unit->id))
<article class="admin-panel">
    @if($unit->image)<img class="admin-unit-photo" src="{{ $unit->image }}" alt="{{ $car->name }} {{ $unit->color }}">@endif
    <div class="admin-unit-top"><span class="unit-swatch" style="background:{{ $unit->color_hex }}"></span><h2>{{ $unit->color }}</h2><span class="admin-status {{ ($stock['available'] ?? false) ? 'admin-status-confirmed' : 'admin-status-demo' }}">{{ $stock['label'] ?? 'Nonaktif' }}</span></div>
    <p class="admin-unit-code">{{ $unit->code }} · {{ $unit->registration ?: 'Nomor polisi belum diisi' }}</p>
    @if($stock['until'] ?? null)<p class="unit-until">Rencana kembali / selesai pemesanan: {{ $stock['until'] }}</p>@endif
    @if($stock['overdue'] ?? false)<p class="unit-stock-notice">Batas waktu terlewati. Unit tetap tidak tersedia sampai pengembalian dicatat.</p>@endif
    <div class="admin-unit-bottom"><span class="admin-unit-code">{{ \App\Models\CarUnit::STATUSES[$unit->status] }}</span><a href="{{ route('admin.units.edit', [$car, $unit]) }}" class="admin-button admin-button-secondary">Edit unit</a></div>
</article>
@empty
<div class="admin-empty admin-panel"><span class="material-symbols-outlined">directions_car</span><h2>Belum ada unit</h2><p>Tambahkan kendaraan agar tipe ini bisa dipesan.</p></div>
@endforelse
</div><form method="post" action="{{ route('admin.units.store', $car) }}" enctype="multipart/form-data" class="admin-panel admin-form admin-unit-create">
    @csrf
    <div class="admin-section-title"><span class="admin-section-number"><span class="material-symbols-outlined">add</span></span><div><h2>Tambah unit</h2><p>Satu unit untuk satu kendaraan fisik.</p></div></div>
    @include('admin.unit-fields', ['unit' => null])
    <button class="admin-button" type="submit">Simpan unit</button>
    <p class="admin-unit-legend">Unit dalam perawatan tidak bisa dipesan. Unit nonaktif disembunyikan dari pilihan penyewa; riwayatnya tetap tersimpan.</p>
</form></div>
@endsection
