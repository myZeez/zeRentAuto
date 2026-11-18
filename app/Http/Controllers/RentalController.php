<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\ReservationRequest;
use App\Models\Booking;
use App\Models\Car;
use App\Models\CarUnit;
use App\Services\RentalQuote;
use App\Services\UnitAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RentalController extends Controller
{
    public function __construct(private RentalQuote $rental) {}

    public function home(): View
    {
        return view('pages.home', ['cars' => $this->rental->cars()]);
    }

    public function catalog(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'in:all,sedan,suv,mpv,sports'],
            'city' => ['nullable', 'in:jakarta,bali,surabaya,bandung'],
            'sort' => ['nullable', 'in:populer,termurah,termahal,terbaru'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'q' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after:start_date'],
            'service' => ['nullable', 'in:self,driver'],
        ]);
        $cars = $this->rental->cars(reservation: ! empty($filters['start_date']) && ! empty($filters['end_date']) ? $filters : null)->filter(fn ($car) => (empty($filters['category']) || $filters['category'] === 'all' || $car['type'] === $filters['category']) &&
            (empty($filters['city']) || $car['city'] === $filters['city']) &&
            (! isset($filters['max_price']) || $car['price'] <= $filters['max_price']) &&
            (empty($filters['q']) || str_contains(strtolower($car['name']), strtolower($filters['q']))));
        $cars = match ($filters['sort'] ?? 'populer') {
            'termurah' => $cars->sortBy('price'), 'termahal' => $cars->sortByDesc('price'), 'terbaru' => $cars->sortByDesc('year'), default => $cars,
        };

        return view('pages.catalog', compact('cars', 'filters'));
    }

    public function detail(Request $request, string $car = 'porsche-718-cayman'): View
    {
        $dates = $request->validate(['start_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'], 'end_date' => ['nullable', 'date_format:Y-m-d', 'after:start_date'], 'service' => ['nullable', 'in:self,driver'], 'start_time' => ['nullable', 'date_format:H:i'], 'end_time' => ['nullable', 'date_format:H:i']]);
        $selected = ['start_date' => old('start_date', $dates['start_date'] ?? today()->addDay()->toDateString()), 'end_date' => old('end_date', $dates['end_date'] ?? today()->addDays(4)->toDateString()), 'start_time' => old('start_time', $dates['start_time'] ?? '09:00'), 'end_time' => old('end_time', $dates['end_time'] ?? '09:00')];
        if (Validator::make($selected, ['start_date' => 'required|date_format:Y-m-d|after_or_equal:today', 'end_date' => 'required|date_format:Y-m-d|after:start_date', 'start_time' => 'required|date_format:H:i', 'end_time' => 'required|date_format:H:i'])->fails()) {
            $selected = ['start_date' => today()->addDay()->toDateString(), 'end_date' => today()->addDays(4)->toDateString(), 'start_time' => '09:00', 'end_time' => '09:00'];
        }
        $dates = array_merge($dates, $selected);
        $model = Car::where('is_active', true)->findOrFail($car);
        $inventory = app(UnitAvailability::class)->inventory($model, $selected);

        return view('pages.detail', ['car' => $model->attributesToArray(), 'dates' => $dates, 'inventory' => $inventory]);
    }

    public function unitAvailability(Request $request, Car $car, UnitAvailability $availability): JsonResponse
    {
        abort_unless($car->is_active, 404);
        $dates = $request->validate(['start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'], 'end_date' => ['required', 'date_format:Y-m-d', 'after:start_date'], 'start_time' => ['nullable', 'date_format:H:i'], 'end_time' => ['nullable', 'date_format:H:i']]);

        return response()->json($availability->inventory($car, $dates))->header('Cache-Control', 'no-store');
    }

    public function reserve(ReservationRequest $request): RedirectResponse
    {
        $unit = CarUnit::findOrFail($request->validated('unit_id'));
        $request->session()->put('reservation', array_merge($request->validated(), $unit->snapshot()));
        $request->session()->put('booking_code', 'ATR-'.strtoupper(Str::random(12)));

        return to_route('checkout');
    }

    public function checkout(Request $request): View|RedirectResponse
    {
        $reservation = $request->session()->get('reservation');
        if (! $reservation) {
            return to_route('catalog');
        }
        if (! $this->rental->cars()->has($reservation['car_id'])) {
            return to_route('catalog')->withErrors(['car_id' => 'Mobil sudah tidak tersedia. Silakan pilih mobil lain.']);
        }
        $unit = CarUnit::find($reservation['unit_id'] ?? null);
        if (! $unit || $unit->car_id !== $reservation['car_id'] || ! app(UnitAvailability::class)->available($unit, $reservation)) {
            return to_route('detail', $reservation['car_id'])->withErrors(['unit_id' => 'Unit sudah tidak tersedia untuk jadwal ini. Pilih unit lain.']);
        }

        return view('pages.checkout', ['reservation' => $reservation, 'car' => $this->rental->car($reservation['car_id']), 'quote' => $this->rental->calculate($reservation)]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $reservation = $request->session()->get('reservation');
        $code = $request->session()->get('booking_code');
        if (! $reservation || ! $code) {
            return to_route('catalog');
        }
        if (! $this->rental->cars()->has($reservation['car_id'])) {
            return to_route('catalog')->withErrors(['car_id' => 'Mobil sudah tidak tersedia. Silakan pilih mobil lain.']);
        }
        if (CarbonImmutable::parse($reservation['start_date'])->isBefore(today())) {
            return to_route('detail', $reservation['car_id'])->withErrors(['start_date' => 'Tanggal reservasi sudah lewat. Pilih tanggal baru.']);
        }
        if (! empty($reservation['start_time']) && CarbonImmutable::parse($reservation['start_date'].' '.$reservation['start_time'], 'Asia/Jakarta')->isPast()) {
            return to_route('detail', $reservation['car_id'])->withErrors(['start_time' => 'Jam pengambilan sudah lewat. Pilih jadwal baru sebelum melanjutkan.']);
        }
        $owner = $request->session()->get('rental_owner', (string) Str::uuid());
        $request->session()->put('rental_owner', $owner);
        $booking = DB::transaction(function () use ($request, $reservation, $code, $owner): Booking {
            $unit = CarUnit::whereKey($reservation['unit_id'] ?? null)->lockForUpdate()->first();
            $existing = Booking::where('code', $code)->first();
            if ($existing) {
                abort_unless($existing->owner_token === $owner, 403);

                return $existing;
            }
            if (! $unit || $unit->car_id !== $reservation['car_id']) {
                throw ValidationException::withMessages(['unit_id' => 'Pilih kembali unit kendaraan dari halaman detail.']);
            }
            app(UnitAvailability::class)->assertAvailable($unit, $reservation);
            $snapshot = array_merge($reservation, $unit->snapshot());

            return Booking::create(['code' => $code, 'owner_token' => $owner, 'reservation' => $snapshot, 'unit_id' => $unit->id, 'quote' => $this->rental->calculate($reservation), 'customer' => $request->safe()->except('terms'), 'status' => 'demo', 'starts_at' => ! empty($reservation['start_time']) ? $reservation['start_date'].' '.$reservation['start_time'] : null, 'due_at' => ! empty($reservation['end_time']) ? $reservation['end_date'].' '.$reservation['end_time'] : null]);
        }, 3);

        return to_route('confirmation', $booking->code);
    }

    private function ownedBooking(Request $request, string $code): Booking
    {
        return Booking::where('code', $code)->where('owner_token', $request->session()->get('rental_owner', ''))->firstOrFail();
    }

    public function confirmation(Request $request, ?string $code = null): View|RedirectResponse
    {
        if (! $code) {
            return to_route('orders');
        }
        $booking = $this->ownedBooking($request, $code);

        return view('pages.confirmation', ['booking' => $booking, 'reservation' => $booking->reservation, 'quote' => $booking->quote, 'car' => $this->rental->car($booking->reservation['car_id'], true)]);
    }

    public function calendar(Request $request, string $code): Response
    {
        $booking = $this->ownedBooking($request, $code);
        $start = CarbonImmutable::parse($booking->reservation['start_date'])->format('Ymd');
        $end = CarbonImmutable::parse($booking->reservation['end_date'])->format('Ymd');
        $stamp = now()->utc()->format('Ymd\\THis\\Z');
        $calendar = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//zeAUTOrent//Rental Demo//ID\r\nBEGIN:VEVENT\r\nUID:{$booking->code}@autora.local\r\nDTSTAMP:{$stamp}\r\nDTSTART;VALUE=DATE:{$start}\r\nDTEND;VALUE=DATE:{$end}\r\nSUMMARY:Reservasi demo zeAUTOrent {$booking->code}\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";

        return response($calendar, 200, ['Content-Type' => 'text/calendar; charset=utf-8', 'Content-Disposition' => 'attachment; filename="'.$booking->code.'.ics"']);
    }

    public function info(string $page): View
    {
        $pages = [
            'cara-sewa' => ['Cara Sewa', 'Pilih mobil, tentukan tanggal dan layanan, lalu isi data pemesan. Reservasi pada aplikasi ini adalah demo dan tidak memesan armada sungguhan.'],
            'tentang-kami' => ['Tentang zeAUTOrent', 'Konsep layanan sewa kendaraan premium untuk perjalanan bisnis dan personal. Situs ini merupakan implementasi demo dari desain zeAUTOrent.'],
            'lokasi-layanan' => ['Lokasi Layanan', 'Katalog demo tersedia untuk Jakarta. Data armada Bali, Surabaya, dan Bandung belum tersedia.'],
            'syarat-dan-ketentuan' => ['Ketentuan Demo', 'Gunakan data contoh. Harga dan layanan merupakan ilustrasi. Reservasi demo tidak menjadi kontrak sewa dan tidak menagih pembayaran.'],
            'kebijakan-privasi' => ['Privasi Demo', 'Data pemesan disimpan terenkripsi pada database lokal. Riwayat dapat dibuka dari browser yang membuat booking atau melalui verifikasi nomor telepon dan kode booking. Akses verifikasi berlaku 30 menit. Jangan masukkan dokumen identitas atau data pembayaran asli.'],
            'keamanan' => ['Keamanan', 'Formulir dilindungi CSRF dan validasi server. Aplikasi tidak meminta nomor kartu atau dokumen identitas.'],
            'klaim-asuransi' => ['Proteksi Asuransi', 'Pilihan proteksi merupakan simulasi harga, bukan polis asuransi aktif.'],
            'layanan-supir' => ['Layanan Supir', 'Simulasi layanan supir dikenakan Rp 350.000 per hari dan bisa dipilih saat reservasi.'],
            'bantuan-24-7' => ['Pusat Bantuan', 'Mulai dari Cari Mobil untuk mencoba alur reservasi. Pembayaran, notifikasi, dan operasional concierge belum terhubung.'],
            'hubungi-kami' => ['Hubungi Kami', 'Kontak dan alamat pada desain adalah contoh. Kanal dukungan produksi belum dikonfigurasi.'],
            'profil-akun' => ['Akun', 'Buka Status & Riwayat untuk melihat proses booking, jadwal sewa, dan pengembalian. Gunakan nomor telepon dan kode booking saat membuka dari perangkat lain.'],
        ];
        abort_unless(isset($pages[$page]), 404);

        return view('pages.info', ['title' => $pages[$page][0], 'description' => $pages[$page][1]]);
    }
}
