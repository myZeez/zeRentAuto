<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\CarUnit;
use App\Services\RentalQuote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Booking> */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $reservation = ['car_id' => 'porsche-718-cayman', 'start_date' => today()->addDay()->toDateString(), 'end_date' => today()->addDays(4)->toDateString(), 'service' => 'self', 'location' => 'Senopati Suites Showroom', 'insurance' => 1];
        $unit = CarUnit::where('car_id', $reservation['car_id'])->first();
        if ($unit) {
            $reservation = array_merge($reservation, $unit->snapshot());
        }

        return ['code' => 'ATR-'.strtoupper(Str::random(12)), 'owner_token' => (string) Str::uuid(), 'unit_id' => $unit?->id, 'reservation' => $reservation, 'quote' => app(RentalQuote::class)->calculate($reservation), 'customer' => ['name' => fake()->name(), 'email' => fake()->safeEmail(), 'phone' => '081234567890', 'notes' => null, 'payment_channel' => 'va'], 'status' => 'demo'];
    }
}
