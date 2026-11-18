<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Car;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class RentalQuote
{
    public function cars(bool $includeInactive = false, ?array $reservation = null): Collection
    {
        return Car::query()->with(['units.bookings' => fn ($query) => $query->whereIn('status', Booking::BLOCKING_STATUSES)])->when(! $includeInactive, fn ($query) => $query->where('is_active', true))->orderBy('sort_order')->orderBy('id')->get()->map(fn (Car $car) => $car->attributesToArray() + ['inventory' => app(UnitAvailability::class)->inventory($car, $reservation)])->keyBy('id');
    }

    public function car(string $id, bool $includeInactive = false): array
    {
        return $this->cars($includeInactive)->get($id) ?? abort(404);
    }

    public function calculate(array $reservation): array
    {
        $car = $this->car($reservation['car_id']);
        $days = (int) CarbonImmutable::parse($reservation['start_date'])->diffInDays(CarbonImmutable::parse($reservation['end_date']));
        $base = $car['price'] * $days;
        $insurance = ! empty($reservation['insurance']) ? 250000 * $days : 0;
        $driver = $reservation['service'] === 'driver' ? 350000 * $days : 0;
        $valet = ! empty($reservation['valet']) ? 150000 : 0;
        $fuel = ! empty($reservation['fuel']) ? 500000 : 0;
        $discount = 500000;
        $deposit = 2500000;
        $subtotal = $base + $insurance + $driver + $valet + $fuel - $discount;

        return compact('days', 'base', 'insurance', 'driver', 'valet', 'fuel', 'discount', 'deposit', 'subtotal') + ['total' => $subtotal + $deposit];
    }
}
