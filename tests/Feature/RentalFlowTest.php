<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CarUnit;
use App\Services\RentalQuote;
use Database\Seeders\CarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected $seeder = CarSeeder::class;

    private function reservation(array $overrides = []): array
    {
        return array_merge(['car_id' => 'porsche-718-cayman', 'unit_id' => CarUnit::where('car_id', 'porsche-718-cayman')->first()->id, 'start_date' => today()->addDay()->toDateString(), 'end_date' => today()->addDays(4)->toDateString(), 'service' => 'self', 'location' => 'Senopati Suites Showroom', 'insurance' => 1], $overrides);
    }

    private function customer(array $overrides = []): array
    {
        return array_merge(['name' => 'Pemesan Contoh', 'email' => 'contoh@example.com', 'phone' => '081234567890', 'notes' => 'Kursi tambahan', 'payment_channel' => 'va', 'terms' => 1], $overrides);
    }

    public function test_public_pages_and_every_car_render(): void
    {
        foreach (['/', '/cari-mobil', '/favorit', '/pesanan-saya', '/informasi/cara-sewa', '/informasi/syarat-dan-ketentuan'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (app(RentalQuote::class)->cars() as $car) {
            $this->get('/detail-mobil/'.$car['id'])->assertOk()->assertSee($car['name']);
        }
        $this->get('/detail-mobil/unknown')->assertNotFound();
        $this->get('/booking-checkout')->assertRedirect(route('catalog'));
    }

    public function test_catalog_filters_and_sorts_actual_inventory(): void
    {
        $this->get('/cari-mobil?category=sports')->assertOk()->assertViewHas('cars', fn ($cars) => $cars->count() === 1 && $cars->first()['id'] === 'porsche-718-cayman');
        $this->get('/cari-mobil?city=bali')->assertOk()->assertSee('Mobil belum ditemukan');
        $this->get('/cari-mobil?sort=termurah')->assertViewHas('cars', fn ($cars) => $cars->first()['price'] === 1950000);
        $this->get('/cari-mobil?max_price=2000000')->assertViewHas('cars', fn ($cars) => $cars->count() === 1);
    }

    public function test_complete_booking_calculates_on_server_and_deduplicates_submission(): void
    {
        $this->post('/reservasi', $this->reservation(['service' => 'driver', 'valet' => 1, 'fuel' => 1, 'total' => 1]))->assertRedirect(route('checkout'));
        $this->get('/booking-checkout')->assertOk()->assertSee('Rp 18.700.000');
        $this->post('/booking-checkout', $this->customer(['total' => 1, 'status' => 'paid']))->assertRedirect();
        $booking = Booking::firstOrFail();
        $this->assertSame(18700000, $booking->quote['total']);
        $this->assertSame('demo', $booking->status);
        $this->assertStringNotContainsString('contoh@example.com', $booking->getRawOriginal('customer'));
        $this->get(route('confirmation', $booking->code))->assertOk()->assertSee('Pemesan Contoh')->assertSee('Rp 18.700.000');
        $this->get(route('calendar', $booking->code))->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8')->assertSee('BEGIN:VCALENDAR')->assertSee('DTSTART;VALUE=DATE:'.today()->addDay()->format('Ymd'));
        $this->get('/pesanan-saya')->assertOk()->assertSee($booking->code);
        $this->post('/booking-checkout', $this->customer())->assertRedirect();
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_invalid_dates_and_unknown_cars_are_rejected(): void
    {
        $this->post('/reservasi', $this->reservation(['start_date' => today()->subDay()->toDateString()]))->assertSessionHasErrors('start_date');
        $this->post('/reservasi', $this->reservation(['end_date' => today()->toDateString()]))->assertSessionHasErrors('end_date');
        $this->post('/reservasi', $this->reservation(['car_id' => 'unknown']))->assertSessionHasErrors('car_id');
        $this->post('/reservasi', $this->reservation(['service' => 'free']))->assertSessionHasErrors('service');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_checkout_requires_valid_customer_and_consent(): void
    {
        $this->post('/reservasi', $this->reservation());
        $this->post('/booking-checkout', $this->customer(['email' => 'invalid', 'phone' => 'abc', 'terms' => 0]))->assertSessionHasErrors(['email', 'phone', 'terms']);
        $this->post('/booking-checkout', $this->customer(['phone' => '() -- () --']))->assertSessionHasErrors('phone');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_is_only_visible_to_its_owner_session(): void
    {
        $booking = Booking::factory()->create();
        $this->get(route('confirmation', $booking->code))->assertNotFound();
        $this->get(route('calendar', $booking->code))->assertNotFound();
        $this->get('/pesanan-saya')->assertDontSee($booking->code);
        $this->withSession(['rental_owner' => $booking->owner_token])->get(route('confirmation', $booking->code))->assertOk();
    }

    public function test_expired_draft_cannot_be_submitted(): void
    {
        $this->withSession(['reservation' => $this->reservation(['start_date' => today()->subDay()->toDateString()]), 'booking_code' => 'ATR-EXPIRED'])
            ->post('/booking-checkout', $this->customer())->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('bookings', 0);
    }
}
