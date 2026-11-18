<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Services\BookingPhone;
use Database\Seeders\CarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected $seeder = CarSeeder::class;

    public function test_phone_requires_a_matching_code_and_never_exposes_other_numbers(): void
    {
        $first = Booking::factory()->create(['admin_notes' => 'Catatan rahasia operasional']);
        $second = Booking::factory()->create();
        $customer = $first->customer;
        $customer['phone'] = '089998887777';
        $unrelated = Booking::factory()->create(['customer' => $customer]);
        $this->get(route('booking-status'))->assertOk()->assertDontSee($first->code)->assertDontSee('id="welcome-screen"', false)->assertHeader('Cache-Control', 'no-store, private');
        $this->post(route('booking-status.lookup'), ['phone' => '081234567890'])->assertSessionHasErrors('code');
        $this->post(route('booking-status.lookup'), ['phone' => '089998887777', 'code' => $first->code])->assertSessionHasErrors('code');
        $this->get(route('booking-status'))->assertViewHas('bookings', fn ($bookings) => $bookings->isEmpty());
        $this->post(route('booking-status.lookup'), ['phone' => '+62 (812) 3456-7890', 'code' => strtolower($first->code)])->assertRedirect(route('booking-status'))->assertSessionHas('booking_access');
        $this->get(route('booking-status'))->assertSee($first->code)->assertDontSee($second->code)->assertDontSee($unrelated->code)->assertDontSee('Catatan rahasia operasional')->assertDontSee($first->customer['email'])->assertViewHas('stats', fn ($stats) => $stats['total'] === 1);
        $this->assertSame(BookingPhone::hash('081234567890'), $first->phone_hash);
        $this->assertStringNotContainsString('081234567890', $first->getRawOriginal('customer'));
    }

    public function test_access_expires_and_can_be_closed_without_logging_out_admin(): void
    {
        $booking = Booking::factory()->create();
        $this->post(route('booking-status.lookup'), ['phone' => '081234567890', 'code' => $booking->code]);
        $this->travel(31)->minutes();
        $this->get(route('booking-status'))->assertDontSee($booking->code)->assertSessionMissing('booking_access');
        $this->travelBack();
        $this->actingAs($admin = User::factory()->admin()->create())->withSession(['rental_owner' => $booking->owner_token]);
        $this->post(route('booking-status.lookup'), ['phone' => '081234567890', 'code' => $booking->code]);
        $this->post(route('booking-status.close'))->assertRedirect(route('booking-status'))->assertSessionMissing('booking_access')->assertSessionMissing('rental_owner');
        $this->get(route('booking-status'))->assertDontSee($booking->code);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_failed_lookup_revokes_previous_phone_access_and_is_throttled(): void
    {
        $booking = Booking::factory()->create();
        $data = ['phone' => '081234567890', 'code' => $booking->code];
        $this->post(route('booking-status.lookup'), $data);
        $data['code'] = 'ATR-XXXXXXXXXXXX';
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('booking-status.lookup'), $data)->assertSessionHasErrors('code');
        }
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('code'));
        $this->get(route('booking-status'))->assertDontSee($booking->code)->assertSessionMissing('booking_access');
    }

    public function test_existing_browser_owner_keeps_access_to_only_their_bookings(): void
    {
        $booking = Booking::factory()->create();
        $other = Booking::factory()->create();
        $this->withSession(['rental_owner' => $booking->owner_token])->get(route('orders'))->assertOk()->assertSee($booking->code)->assertDontSee($other->code)->assertSee('Jam menunggu konfirmasi');
    }

    public function test_lookup_displays_only_the_exact_booking_even_with_other_bookings_in_the_browser(): void
    {
        $owned = Booking::factory()->create();
        $sameOwner = Booking::factory()->create(['owner_token' => $owned->owner_token, 'status' => 'active']);
        $matched = Booking::factory()->create();
        $samePhone = Booking::factory()->create(['status' => 'completed']);
        $this->withSession(['rental_owner' => $owned->owner_token]);
        $this->post(route('booking-status.lookup'), ['phone' => '081234567890', 'code' => $matched->code])->assertRedirect(route('booking-status'));
        $this->get(route('booking-status'))->assertOk()->assertSee($matched->code)->assertDontSee($owned->code)->assertDontSee($sameOwner->code)->assertDontSee($samePhone->code)->assertViewHas('bookings', fn ($bookings) => $bookings->total() === 1 && $bookings->first()->id === $matched->id)->assertViewHas('stats', ['total' => 1, 'active' => 0, 'completed' => 0]);
        $this->get(route('booking-status', ['page' => 2]))->assertDontSee($samePhone->code)->assertDontSee($owned->code);
        $this->post(route('booking-status.lookup'), ['phone' => '081234567890', 'code' => $samePhone->code])->assertRedirect();
        $this->get(route('booking-status'))->assertSee($samePhone->code)->assertDontSee($matched->code)->assertDontSee($owned->code)->assertViewHas('stats', ['total' => 1, 'active' => 0, 'completed' => 1]);
    }

    public function test_invalid_lookup_and_expired_or_old_access_do_not_fall_back_to_browser_history(): void
    {
        $owned = Booking::factory()->create();
        $matched = Booking::factory()->create();
        $this->withSession(['rental_owner' => $owned->owner_token]);
        $data = ['phone' => '081234567890', 'code' => $matched->code];
        $this->post(route('booking-status.lookup'), $data)->assertRedirect();
        $this->post(route('booking-status.lookup'), ['phone' => 'invalid', 'code' => 'invalid'])->assertSessionHasErrors(['phone', 'code']);
        $this->get(route('booking-status'))->assertDontSee($owned->code)->assertDontSee($matched->code)->assertSessionMissing('booking_access')->assertViewHas('hasAccess', false);
        $this->post(route('booking-status.lookup'), $data)->assertRedirect();
        $this->travel(31)->minutes();
        $this->get(route('booking-status'))->assertDontSee($owned->code)->assertDontSee($matched->code)->assertViewHas('hasAccess', false);
        $this->travelBack();
        $this->withSession(['booking_access' => ['phone_hash' => $matched->phone_hash, 'last_digits' => '7890', 'expires_at' => now()->addMinutes(30)->timestamp]])->get(route('booking-status'))->assertOk()->assertDontSee($owned->code)->assertDontSee($matched->code)->assertSessionMissing('booking_access');
    }

    public function test_admin_schedule_drives_customer_deadline_and_actual_return_information(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 11)->setTime(12, 0));
        $booking = Booking::factory()->create();
        $this->actingAs(User::factory()->admin()->create());
        $data = ['status' => 'active', 'starts_at' => '2026-10-10T10:00', 'due_at' => '2026-10-11T14:30', 'returned_at' => null, 'admin_notes' => 'Secret'];
        $this->put(route('admin.booking.update', $booking), $data)->assertRedirect();
        $this->withSession(['rental_owner' => $booking->owner_token])->get(route('booking-status'))->assertSee('14:30 WIB')->assertSee('Sewa sedang berjalan')->assertDontSee('Terlambat dikembalikan')->assertDontSee('Secret');
        $this->travelTo(now()->setTime(14, 30));
        $this->assertFalse($booking->fresh()->isOverdue());
        $this->travel(1)->minutes();
        $this->get(route('booking-status'))->assertSee('Terlambat dikembalikan');
        $data['status'] = 'completed';
        $data['returned_at'] = '2026-10-11T14:31';
        $this->put(route('admin.booking.update', $booking), $data)->assertRedirect();
        $this->get(route('booking-status'))->assertSee('Kendaraan sudah dikembalikan')->assertSee('14:31')->assertSee('Dikembalikan melewati batas waktu')->assertDontSee('data-rental-deadline', false);
        $this->assertFalse($booking->fresh()->isOverdue());
        $this->assertTrue($booking->fresh()->returnedLate());
    }

    public function test_schedule_validation_prevents_invalid_active_and_completed_bookings(): void
    {
        $booking = Booking::factory()->create();
        $this->actingAs(User::factory()->admin()->create());
        $this->put(route('admin.booking.update', $booking), ['status' => 'active'])->assertSessionHasErrors(['starts_at', 'due_at']);
        $this->put(route('admin.booking.update', $booking), ['status' => 'active', 'starts_at' => now()->addDay()->format('Y-m-d\TH:i'), 'due_at' => now()->addDays(2)->format('Y-m-d\TH:i')])->assertSessionHasErrors('starts_at');
        $this->assertStringContainsString('Dikonfirmasi', session('errors')->first('starts_at'));
        $this->put(route('admin.booking.update', $booking), ['status' => 'completed', 'starts_at' => now()->subDays(2)->format('Y-m-d\TH:i'), 'due_at' => now()->subDays(3)->format('Y-m-d\TH:i')])->assertSessionHasErrors(['due_at', 'returned_at']);
        $this->put(route('admin.booking.update', $booking), ['status' => 'completed', 'starts_at' => now()->subDay()->format('Y-m-d\TH:i'), 'due_at' => now()->addDay()->format('Y-m-d\TH:i'), 'returned_at' => now()->addHour()->format('Y-m-d\TH:i')])->assertSessionHasErrors('returned_at');
        $this->assertSame('demo', $booking->fresh()->status);
    }

    public function test_early_handover_can_start_a_future_booking_and_preserves_the_original_booking(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 11)->setTime(12, 0));
        $booking = Booking::factory()->create(['status' => 'confirmed', 'starts_at' => today()->addDay()->setTime(9, 0), 'due_at' => today()->addDays(4)->setTime(9, 0)]);
        $reservation = $booking->reservation;
        $quote = $booking->quote;
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.booking', $booking))->assertOk()->assertSee('Mobil diserahkan sekarang')->assertSee('Jadwal booking awal tetap tersimpan.');
        $this->put(route('admin.booking.update', $booking), ['status' => 'active', 'start_date' => today()->toDateString(), 'start_time' => '12:00', 'due_date' => $booking->due_at->toDateString(), 'due_time' => '09:00', 'returned_at' => null])->assertSessionHasNoErrors()->assertRedirect();
        $booking->refresh();
        $this->assertSame('active', $booking->status);
        $this->assertSame('2026-10-11 12:00', $booking->starts_at->format('Y-m-d H:i'));
        $this->assertSame($reservation, $booking->reservation);
        $this->assertSame($quote, $booking->quote);
        $this->withSession(['rental_owner' => $booking->owner_token])->get(route('booking-status'))->assertOk()->assertSee('Sedang disewa')->assertSee('12:00 WIB');
    }

    public function test_admin_dates_come_from_the_booking_and_only_times_need_confirmation(): void
    {
        $booking = Booking::factory()->create();
        $reservation = $booking->reservation;
        $quote = $booking->quote;
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.booking', $booking))->assertOk()
            ->assertSee('name="start_date" required value="'.$reservation['start_date'].'"', false)
            ->assertSee('name="due_date" required value="'.$reservation['end_date'].'"', false)
            ->assertSee('name="start_time" value=""', false)
            ->assertSee('name="due_time" value=""', false);
        $data = ['status' => 'confirmed', 'start_date' => $reservation['start_date'], 'due_date' => $reservation['end_date'], 'start_time' => null, 'due_time' => null];
        $this->put(route('admin.booking.update', $booking), $data)->assertRedirect();
        $this->assertNull($booking->fresh()->starts_at);
        $this->assertNull($booking->fresh()->due_at);
        $data['start_time'] = '09:15';
        $data['due_time'] = '17:30';
        $this->put(route('admin.booking.update', $booking), $data)->assertRedirect();
        $booking->refresh();
        $this->assertSame($reservation['start_date'].'T09:15', $booking->starts_at->format('Y-m-d\TH:i'));
        $this->assertSame($reservation['end_date'].'T17:30', $booking->due_at->format('Y-m-d\TH:i'));
        $this->assertSame($reservation, $booking->reservation);
        $this->assertSame($quote, $booking->quote);
        $this->withSession(['rental_owner' => $booking->owner_token])->get(route('booking-status'))->assertSee('09:15 WIB')->assertSee('17:30 WIB');
        $this->get(route('admin.booking', $booking))->assertSee('name="start_time" value="09:15"', false)->assertSee('name="due_time" value="17:30"', false);
    }

    public function test_saved_admin_schedule_is_preserved_and_incomplete_or_invalid_times_are_rejected(): void
    {
        $booking = Booking::factory()->create(['starts_at' => now()->subDay()->setTime(8, 0), 'due_at' => now()->addDay()->setTime(8, 0)]);
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.booking', $booking))
            ->assertSee('name="start_date" required value="'.$booking->starts_at->toDateString().'"', false)
            ->assertSee('name="due_date" required value="'.$booking->due_at->toDateString().'"', false);
        $data = ['status' => 'confirmed', 'start_date' => $booking->starts_at->toDateString(), 'due_date' => $booking->due_at->toDateString(), 'start_time' => '08:00', 'due_time' => null];
        $this->put(route('admin.booking.update', $booking), $data)->assertSessionHasErrors('due_at');
        $data['due_time'] = '27:30';
        $this->put(route('admin.booking.update', $booking), $data)->assertSessionHasErrors('due_time');
        $data['due_time'] = '07:00';
        $data['due_date'] = $data['start_date'];
        $this->put(route('admin.booking.update', $booking), $data)->assertSessionHasErrors('due_at');
        $this->assertSame('demo', $booking->fresh()->status);
        $this->assertSame($booking->due_at->toDateTimeString(), $booking->fresh()->due_at->toDateTimeString());
    }

    public function test_on_time_return_and_cancellation_do_not_show_an_overdue_countdown(): void
    {
        $booking = Booking::factory()->create(['status' => 'completed', 'starts_at' => now()->subDays(3), 'due_at' => now()->subDay(), 'returned_at' => now()->subDays(2)]);
        $this->withSession(['rental_owner' => $booking->owner_token])->get(route('booking-status'))->assertSee('Dikembalikan tepat waktu')->assertDontSee('data-rental-deadline', false);
        $booking->update(['status' => 'cancelled', 'returned_at' => null]);
        $this->get(route('booking-status'))->assertSee('Dibatalkan')->assertDontSee('aria-label="Progres booking"', false)->assertDontSee('Terlambat dikembalikan');
    }

    public function test_legacy_bookings_gain_searchable_phone_hashes_without_changing_customer_or_dates(): void
    {
        $booking = Booking::factory()->create();
        $customer = $booking->customer;
        $updatedAt = $booking->updated_at;
        $migration = require database_path('migrations/2026_10_11_145358_add_tracking_to_bookings_table.php');
        $migration->down();
        $migration->up();
        $booking->refresh();
        $this->assertSame(BookingPhone::hash($customer['phone']), $booking->phone_hash);
        $this->assertSame($customer, $booking->customer);
        $this->assertTrue($booking->updated_at->equalTo($updatedAt));
        $this->assertNull($booking->due_at);
        $customer['phone'] = '+62 899 9888 7777';
        $booking->update(['customer' => $customer]);
        $this->assertSame(BookingPhone::hash('089998887777'), DB::table('bookings')->where('id', $booking->id)->value('phone_hash'));
    }
}
