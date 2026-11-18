<footer class="mt-space-2xl w-full bg-surface-container-lowest">
    <div class="mx-auto max-w-[1360px] px-margin-mobile pt-space-2xl pb-space-xl md:px-margin">
        <div class="grid grid-cols-1 gap-space-xl border-b border-border-subtle pb-space-2xl md:grid-cols-2">
            <section class="space-y-space-md" aria-label="Tentang zeAUTOrent">
                <a href="{{ route('home') }}" aria-label="zeAUTOrent — Beranda">
                    @include('partials.brand')
                </a>
                <p class="max-w-sm text-body-sm text-on-surface-variant">Layanan rental kendaraan premium dan eksekutif di Indonesia untuk perjalanan bisnis maupun personal.</p>
                <div class="space-y-1.5 text-body-sm text-on-surface">
                    <p class="font-label-sm uppercase tracking-wider text-on-surface-variant">Kantor Layanan</p>
                    <p><span class="font-medium">Jakarta:</span> Senopati Suites Tower 2, Kebayoran Baru</p>
                    <p><span class="font-medium">Bali:</span> Sunset Road Arcade No. 88, Kuta</p>
                    <p><span class="font-medium">Klimantan Tegah:</span> Palangka Raya</p>
                </div>
            </section>
            <section class="space-y-space-sm text-body-sm text-on-surface-variant" aria-label="Kontak layanan">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Customer Care</h2>
                <p class="font-semibold text-on-surface">+62 21 5088 9900</p>
                <p>Kontak layanan segera tersedia</p>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-sage-subtle px-2.5 py-1 font-label-sm text-accent-sage">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-sage"></span>Operasional Siaga 24 Jam
                </span>
            </section>
        </div>
        <p class="no-print mt-space-lg rounded-xl bg-accent-sage-subtle px-4 py-3 text-center text-xs text-accent-sage">Pratinjau zeAUTOrent · Data armada &amp; layanan demo · Tanpa transaksi nyata</p>
        <p class="pt-space-lg text-center text-body-sm text-on-surface-variant">© {{ date('Y') }} zeAUTOrent Indonesia. Seluruh hak cipta dilindungi undang-undang.</p>
        <p class="no-print pt-3 text-center text-xs text-on-surface-variant"><a href="{{ route('booking-status') }}" class="hover:text-accent-sage">Status &amp; Riwayat</a> · <a href="{{ route('login') }}" class="hover:text-accent-sage">Login Admin</a></p>
    </div>
</footer>

