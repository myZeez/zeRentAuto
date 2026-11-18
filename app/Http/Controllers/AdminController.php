<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\Car;
use App\Models\CarUnit;
use App\Services\UnitAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'stats' => ['cars' => Car::where('is_active', true)->count(), 'new' => Booking::where('status', 'demo')->count(), 'active' => Booking::where('status', 'active')->count(), 'total' => Booking::count()],
            'bookings' => Booking::latest()->limit(5)->get(),
            'cars' => Car::all()->keyBy('id'),
        ]);
    }

    public function cars(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'active' => ['nullable', 'in:all,1,0']]);
        $cars = Car::query()->withCount('units')->when($filters['q'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->when(isset($filters['active']) && $filters['active'] !== 'all', fn ($query) => $query->where('is_active', $filters['active']))
            ->orderBy('sort_order')->paginate(12)->withQueryString();

        return view('admin.cars', compact('cars', 'filters'));
    }

    public function createCar(): View
    {
        return view('admin.car-form', ['car' => new Car(['is_active' => true, 'is_featured' => false, 'seats' => 5, 'year' => now()->year, 'city' => 'jakarta', 'type' => 'sedan', 'sort_order' => (Car::max('sort_order') ?? 0) + 1])]);
    }

    public function editCar(Car $car): View
    {
        return view('admin.car-form', compact('car'));
    }

    private function carData(CarRequest $request): array
    {
        $data = $request->safe()->except('image_upload');
        if ($request->hasFile('image_upload')) {
            $data['image'] = '/storage/'.$request->file('image_upload')->store('cars', 'public');
        }

        return $data;
    }

    public function storeCar(CarRequest $request): RedirectResponse
    {
        Car::create($this->carData($request) + ['id' => Str::slug($request->string('name')->toString()).'-'.Str::lower(Str::random(6))]);

        return to_route('admin.cars')->with('status', 'Mobil berhasil ditambahkan.');
    }

    public function updateCar(CarRequest $request, Car $car): RedirectResponse
    {
        $car->update($this->carData($request));

        return to_route('admin.cars')->with('status', 'Perubahan mobil berhasil disimpan.');
    }

    public function bookings(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::in(array_merge(['all'], array_keys(Booking::STATUSES)))]]);
        $bookings = Booking::query()->when($filters['q'] ?? null, fn ($query, $search) => $query->where('code', 'like', '%'.$search.'%'))
            ->when(isset($filters['status']) && $filters['status'] !== 'all', fn ($query) => $query->where('status', $filters['status']))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.bookings', ['bookings' => $bookings, 'filters' => $filters, 'cars' => Car::all()->keyBy('id')]);
    }

    public function booking(Booking $booking): View
    {
        return view('admin.booking', ['booking' => $booking, 'car' => Car::find($booking->reservation['car_id']), 'units' => CarUnit::where('car_id', $booking->reservation['car_id'])->orderBy('id')->get()]);
    }

    public function updateBooking(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        DB::transaction(function () use ($request, $booking): void {
            $data = $request->safe()->only(['status', 'admin_notes', 'starts_at', 'due_at', 'returned_at', 'unit_id']);
            $unitId = $data['unit_id'] ?? $booking->unit_id;
            $data['unit_id'] = $unitId;
            $locked = CarUnit::where('car_id', $booking->reservation['car_id'])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $unit = $locked->get($unitId);
            if ($unit && in_array($data['status'], Booking::BLOCKING_STATUSES, true)) {
                $startsAt = array_key_exists('starts_at', $data) ? ($data['starts_at'] ? CarbonImmutable::parse($data['starts_at']) : null) : $booking->starts_at;
                $dueAt = array_key_exists('due_at', $data) ? ($data['due_at'] ? CarbonImmutable::parse($data['due_at']) : null) : $booking->due_at;
                app(UnitAvailability::class)->assertAvailable($unit, $booking->reservation, $booking->id, $startsAt, $dueAt);
            }
            if ($unit) {
                $data['reservation'] = array_merge($booking->reservation, $unit->snapshot());
            }
            $booking->update($data);
        }, 3);

        return to_route('admin.booking', $booking)->with('status', 'Reservasi berhasil diperbarui.');
    }

    public function account(): View
    {
        return view('admin.account');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'string', 'min:12', 'max:255', 'confirmed', 'different:current_password']]);
        $request->user()->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();
        $request->session()->regenerate();

        return to_route('admin.account')->with('status', 'Kata sandi berhasil diperbarui.');
    }
}
