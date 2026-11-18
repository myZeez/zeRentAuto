<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Car;
use App\Models\CarUnit;
use App\Models\User;
use App\Services\UnitAvailability;
use Database\Seeders\CarSeeder;
use Database\Seeders\CarUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected $seeder = CarSeeder::class;

    private function reservation(array $changes = []): array
    {
        return array_replace(['car_id' => 'porsche-718-cayman', 'unit_id' => CarUnit::where('car_id', 'porsche-718-cayman')->first()->id, 'start_date' => today()->addDay()->toDateString(), 'end_date' => today()->addDays(4)->toDateString(), 'start_time' => '09:00', 'end_time' => '09:00', 'location' => 'Senopati Suites Showroom', 'service' => 'self'], $changes);
    }

    private function stock(array $dates = []): array
    {
        return app(UnitAvailability::class)->inventory(Car::findOrFail('porsche-718-cayman'), $this->reservation($dates));
    }

    private function customer(): array
    {
        return ['name' => 'Demo Unit', 'email' => 'unit@example.test', 'phone' => '081234567890', 'payment_channel' => 'va', 'terms' => 1];
    }

    public function test_two_colors_per_type_are_seeded_without_overwriting_admin_changes(): void
    {
        $this->assertDatabaseCount('car_units', 12);
        foreach (Car::all() as $car) {
            $this->assertSame(['Silver', 'Hitam'], $car->units()->pluck('color')->all());
        }
        $unit = CarUnit::first();
        $unit->update(['color' => 'Putih', 'status' => 'maintenance']);
        $this->seed(CarUnitSeeder::class);
        $this->assertDatabaseCount('car_units', 12);
        $this->assertSame('Putih', $unit->fresh()->color);
        $this->assertSame('maintenance', $unit->fresh()->status);
    }

    public function test_chosen_unit_and_hours_are_saved_and_only_that_unit_is_reserved(): void
    {
        $reservation = $this->reservation();
        $this->post(route('reserve'), $reservation)->assertRedirect(route('checkout'));
        $this->post(route('booking.store'), $this->customer())->assertRedirect();
        $booking = Booking::firstOrFail();
        $this->assertSame($reservation['unit_id'], $booking->unit_id);
        $this->assertSame('Silver', $booking->reservation['unit_color']);
        $this->assertSame($reservation['start_date'].' 09:00', $booking->starts_at->format('Y-m-d H:i'));
        $this->assertSame($reservation['end_date'].' 09:00', $booking->due_at->format('Y-m-d H:i'));
        $stock = $this->stock();
        $this->assertSame(1, $stock['available']);
        $this->assertFalse($stock['units'][0]['available']);
        $this->assertTrue($stock['units'][1]['available']);
        $this->get(route('confirmation', $booking->code))->assertOk()->assertSee('Silver')->assertSee($booking->reservation['unit_code']);
        $this->get(route('booking-status'))->assertOk()->assertSee($booking->reservation['unit_code']);
    }

    public function test_both_units_reserved_display_zero_and_reject_another_booking(): void
    {
        foreach (CarUnit::where('car_id', 'porsche-718-cayman')->get() as $unit) {
            Booking::factory()->create(['unit_id' => $unit->id, 'reservation' => $this->reservation(['unit_id' => $unit->id])]);
        }
        $this->assertSame(0, $this->stock()['available']);
        $this->post(route('reserve'), $this->reservation())->assertSessionHasErrors('unit_id');
        $this->get(route('detail', 'porsche-718-cayman'))->assertOk()->assertSee('Semua unit tidak tersedia');
    }

    public function test_dates_and_hours_can_be_adjacent_but_cannot_overlap(): void
    {
        Booking::factory()->create(['reservation' => $this->reservation()]);
        $this->assertSame(2, $this->stock(['start_date' => today()->addDays(4)->toDateString(), 'end_date' => today()->addDays(5)->toDateString(), 'start_time' => '09:00'])['available']);
        $this->assertSame(1, $this->stock(['start_date' => today()->addDays(4)->toDateString(), 'end_date' => today()->addDays(5)->toDateString(), 'start_time' => '08:59'])['available']);
        $this->post(route('reserve'), $this->reservation(['start_date' => today()->addDays(4)->toDateString(), 'end_date' => today()->addDays(5)->toDateString()]))->assertRedirect(route('checkout'));
    }

    public function test_stale_checkout_draft_is_rechecked_when_submitted(): void
    {
        $this->post(route('reserve'), $this->reservation())->assertRedirect(route('checkout'));
        Booking::factory()->create(['reservation' => $this->reservation()]);
        $this->post(route('booking.store'), $this->customer())->assertSessionHasErrors('unit_id');
        $this->assertDatabaseCount('bookings', 1);
        $this->get(route('checkout'))->assertRedirect(route('detail', 'porsche-718-cayman'));
    }

    public function test_wrong_type_missing_unit_and_past_pickup_hour_are_rejected(): void
    {
        $this->post(route('reserve'), $this->reservation(['unit_id' => null]))->assertSessionHasErrors('unit_id');
        $otherUnit = CarUnit::where('car_id', '!=', 'porsche-718-cayman')->first();
        $this->post(route('reserve'), $this->reservation(['unit_id' => $otherUnit->id]))->assertSessionHasErrors('unit_id');
        $this->travelTo(today()->setTime(12, 0));
        $this->post(route('reserve'), $this->reservation(['start_date' => today()->toDateString(), 'start_time' => '09:00']))->assertSessionHasErrors('start_time');
    }

    public function test_checkout_rejects_a_pickup_hour_that_elapsed_after_selecting_a_unit(): void
    {
        $this->travelTo(today()->setTime(8, 0));
        $this->post(route('reserve'), $this->reservation(['start_date' => today()->toDateString()]))->assertRedirect(route('checkout'));
        $this->travelTo(today()->setTime(10, 0));
        $this->post(route('booking.store'), $this->customer())->assertSessionHasErrors('start_time');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_cancellation_and_completion_free_stock_but_overdue_rental_blocks_future_dates(): void
    {
        $booking = Booking::factory()->create(['reservation' => $this->reservation()]);
        $booking->update(['status' => 'cancelled']);
        $this->assertSame(2, $this->stock()['available']);
        $booking->update(['status' => 'active', 'starts_at' => now()->subDays(3), 'due_at' => now()->subHour()]);
        $stock = $this->stock();
        $this->assertSame(1, $stock['available']);
        $this->assertTrue($stock['units'][0]['overdue']);
        $this->assertSame('Menunggu pengembalian', $stock['units'][0]['label']);
        $booking->update(['status' => 'completed', 'returned_at' => now()]);
        $this->assertSame(2, $this->stock()['available']);
    }

    public function test_public_inventory_excludes_private_data_and_reflects_selected_dates(): void
    {
        $unit = CarUnit::where('car_id', 'porsche-718-cayman')->first();
        $unit->update(['registration' => 'B 9999 PRIVATE']);
        $booking = Booking::factory()->create(['reservation' => $this->reservation()]);
        $response = $this->getJson(route('units.availability', ['car' => 'porsche-718-cayman'] + $this->reservation()));
        $response->assertOk()->assertJsonPath('total', 2)->assertJsonPath('available', 1)->assertJsonPath('units.0.label', 'Sudah dipesan');
        $response->assertDontSee('B 9999 PRIVATE')->assertDontSee($booking->code)->assertDontSee($booking->customer['email']);
        $this->get(route('catalog', ['start_date' => $this->reservation()['start_date'], 'end_date' => $this->reservation()['end_date']]))->assertViewHas('cars', fn ($cars) => $cars['porsche-718-cayman']['inventory']['available'] === 1);
    }

    public function test_maintenance_and_inactive_units_are_not_bookable(): void
    {
        $units = CarUnit::where('car_id', 'porsche-718-cayman')->get();
        $units[0]->update(['status' => 'maintenance']);
        $this->assertSame(1, $this->stock()['available']);
        $this->post(route('reserve'), $this->reservation())->assertSessionHasErrors('unit_id');
        $units[1]->update(['status' => 'inactive']);
        $this->assertSame(1, $this->stock()['total']);
        $this->assertSame(0, $this->stock()['available']);
    }

    public function test_admin_manages_units_with_role_checks_and_vehicle_scoping(): void
    {
        $car = Car::findOrFail('porsche-718-cayman');
        $data = ['color' => 'Putih', 'color_hex' => '#ffffff', 'registration' => 'B 1234 ZE', 'status' => 'ready'];
        $this->post(route('admin.units.store', $car), $data)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post(route('admin.units.store', $car), $data)->assertForbidden();
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.units', $car))->assertOk()->assertSee('Silver')->assertSee('Hitam');
        $this->post(route('admin.units.store', $car), $data)->assertRedirect(route('admin.units', $car));
        $unit = $car->units()->where('color', 'Putih')->firstOrFail();
        $this->get(route('admin.units.edit', [$car, $unit]))->assertOk()->assertSee('B 1234 ZE');
        $this->put(route('admin.units.update', [$car, $unit]), $data + ['code' => $unit->code])->assertRedirect(route('admin.units', $car));
        $this->put(route('admin.units.update', [Car::where('id', '!=', $car->id)->first(), $unit]), $data)->assertNotFound();
        $this->assertSame(3, $this->stock()['total']);
    }

    public function test_admin_cannot_assign_occupied_unit_or_extend_into_another_reservation(): void
    {
        $units = CarUnit::where('car_id', 'porsche-718-cayman')->get();
        $first = Booking::factory()->create(['unit_id' => $units[0]->id, 'reservation' => $this->reservation()]);
        $second = Booking::factory()->create(['unit_id' => $units[1]->id, 'reservation' => $this->reservation(['unit_id' => $units[1]->id])]);
        $this->actingAs(User::factory()->admin()->create());
        $this->put(route('admin.booking.update', $second), ['status' => 'confirmed', 'unit_id' => $units[0]->id])->assertSessionHasErrors('unit_id');
        $this->assertSame($units[1]->id, $second->fresh()->unit_id);
        Booking::factory()->create(['unit_id' => $units[0]->id, 'reservation' => $this->reservation(['start_date' => today()->addDays(5)->toDateString(), 'end_date' => today()->addDays(6)->toDateString()])]);
        $this->put(route('admin.booking.update', $first), ['status' => 'confirmed', 'starts_at' => today()->addDay()->format('Y-m-d').'T09:00', 'due_at' => today()->addDays(6)->format('Y-m-d').'T09:00'])->assertSessionHasErrors('unit_id');
        $this->get(route('admin.booking', $first))->assertOk()->assertSee('Sedang disewa')->assertSee('Unit kendaraan');
    }

    public function test_migration_assigns_legacy_bookings_without_changing_customer_costs_or_dates(): void
    {
        $booking = Booking::factory()->create();
        $customer = $booking->getRawOriginal('customer');
        $quote = $booking->quote;
        $reservation = $booking->reservation;
        $migration = require database_path('migrations/2026_10_11_172513_create_car_units_and_assign_bookings.php');
        $migration->down();
        $migration->up();
        $booking->refresh();
        $this->assertNotNull($booking->unit_id);
        $this->assertSame($customer, $booking->getRawOriginal('customer'));
        $this->assertSame($quote, $booking->quote);
        $this->assertSame($reservation['start_date'], $booking->reservation['start_date']);
        $this->assertSame($reservation['end_date'], $booking->reservation['end_date']);
        $this->assertSame($booking->unit->color, $booking->reservation['unit_color']);
    }
}
