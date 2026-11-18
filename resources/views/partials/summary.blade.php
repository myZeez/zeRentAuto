@include('partials.selected-unit')
<div class="space-y-3 text-body-sm">
    @foreach(['base' => 'Sewa pokok ('.$quote['days'].' hari)', 'insurance' => 'Proteksi Zero Excess', 'driver' => 'Layanan supir', 'valet' => 'Antar-jemput valet', 'fuel' => 'Full tank prepaid'] as $key => $label)
        @if($quote[$key] > 0)<div class="flex justify-between gap-4"><span class="text-ink-secondary">{{ $label }}</span><span class="font-semibold whitespace-nowrap">Rp {{ number_format($quote[$key], 0, ',', '.') }}</span></div>@endif
    @endforeach
    <div class="flex justify-between text-accent-sage gap-4"><span>Diskon demo zeAUTOrent</span><span>−Rp {{ number_format($quote['discount'], 0, ',', '.') }}</span></div>
    <div class="flex justify-between gap-4 border-t border-border-subtle pt-3"><span>Subtotal sewa</span><span class="font-semibold">Rp {{ number_format($quote['subtotal'], 0, ',', '.') }}</span></div>
    <div class="flex justify-between gap-4 bg-canvas-offwhite rounded-lg p-3"><span>Deposit (refundable)</span><span class="font-semibold whitespace-nowrap">Rp {{ number_format($quote['deposit'], 0, ',', '.') }}</span></div>
    <div class="rounded-xl bg-accent-sage-subtle p-4"><p class="text-label-sm uppercase text-accent-sage font-bold">Total simulasi, termasuk deposit</p><p class="font-currency-display text-3xl mt-1">Rp {{ number_format($quote['total'], 0, ',', '.') }}</p></div>
    <p class="text-xs text-ink-secondary">Demo — tidak ada pembayaran yang ditagihkan.</p>
</div>
