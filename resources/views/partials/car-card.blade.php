<article class="bg-surface rounded-2xl p-space-md shadow-sm hover:shadow-md transition-all flex flex-col justify-between group" data-car-card="{{ $car['id'] }}">
    <div>
        <div class="flex items-center justify-between mb-2"><span class="rounded-full bg-accent-sage-subtle text-accent-sage px-3 py-1 text-label-sm">● Armada Demo</span><button type="button" data-favorite="{{ $car['id'] }}" aria-label="Simpan {{ $car['name'] }} ke favorit" aria-pressed="false" class="p-2 rounded-full hover:bg-subtle-gray"><span class="material-symbols-outlined">favorite</span></button></div>
        <a href="{{ route('detail', ['car' => $car['id']] + request()->only('start_date', 'end_date', 'service')) }}" class="block aspect-[16/10] rounded-xl bg-canvas-offwhite overflow-hidden p-3 mb-4"><img src="{{ $car['image'] }}" alt="{{ $car['name'] }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" loading="lazy"></a>
        <span class="text-label-sm text-ink-secondary uppercase tracking-wider">{{ $car['category'] }}</span>
        <h3 class="font-headline-sm text-headline-sm font-bold mt-1">{{ $car['name'] }}</h3>
        <p class="text-body-sm text-ink-secondary mt-1">{{ $car['specs'] }}</p>
        <p class="unit-card-stock"><span class="material-symbols-outlined" aria-hidden="true">garage</span>{{ $car['inventory']['available'] }} tersedia / {{ $car['inventory']['total'] }} unit {{ request()->filled('start_date') && request()->filled('end_date') ? '· jadwal pilihan' : '· saat ini' }}</p>
        <div class="flex items-center justify-between text-label-sm bg-canvas-offwhite rounded-xl p-3 my-4"><span>{{ $car['seats'] }} Kursi</span><span>Otomatis</span><span>{{ str_contains($car['name'], 'IONIQ') ? 'Listrik' : 'Bensin' }}</span></div>
    </div>
    <div class="flex items-end justify-between gap-2"><div><span class="text-label-sm text-ink-secondary">Tarif Sewa / hari</span><p class="font-currency-display text-currency-display">Rp {{ number_format($car['price'], 0, ',', '.') }}</p></div><a href="{{ route('detail', ['car' => $car['id']] + request()->only('start_date', 'end_date', 'service')) }}" class="bg-primary text-white rounded-xl px-4 py-2 text-label-md hover:bg-inverse-surface">Detail</a></div>
</article>
