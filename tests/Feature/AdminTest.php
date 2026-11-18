<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Car;
use App\Models\User;
use Database\Seeders\CarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected $seeder = CarSeeder::class;

    private function carData(array $changes = []): array
    {
        return array_replace(['name' => 'Toyota Alphard Baru', 'image' => '/images/hero-mercedes.png', 'price' => 2800000, 'category' => 'Executive MPV', 'specs' => 'Hybrid · Ottoman lounge', 'seats' => 7, 'year' => 2025, 'city' => 'bali', 'type' => 'mpv', 'is_active' => 1, 'is_featured' => 1, 'sort_order' => 0], $changes);
    }

    public function test_login_is_separate_and_admin_routes_require_an_administrator(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Selamat datang')->assertDontSee('welcome-screen');
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->post(route('admin.cars.store'), $this->carData())->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
        $this->post(route('admin.cars.store'), $this->carData())->assertForbidden();
        $this->assertDatabaseCount('cars', 6);
    }

    public function test_admin_can_login_and_logout_but_regular_users_cannot_login_to_admin(): void
    {
        $regular = User::factory()->create();
        $this->post(route('login.store'), ['email' => $regular->email, 'password' => 'password', 'is_admin' => 1])->assertSessionHasErrors('email');
        $this->assertGuest();
        $admin = User::factory()->admin()->create();
        $this->post(route('login.store'), ['email' => strtoupper($admin->email), 'password' => 'password', 'remember' => 1])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Reservasi masuk')->assertSee('Armada aktif')->assertDontSee('welcome-screen');
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_wrong_credentials_are_throttled_and_password_is_not_flashed(): void
    {
        $admin = User::factory()->admin()->create();
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'wrong-password'])->assertSessionHasErrors('email');
        }
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
        $this->assertArrayNotHasKey('password', session('_old_input', []));
        $this->assertGuest();
    }

    public function test_admin_can_create_edit_and_feature_a_car_in_the_public_catalog(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->post(route('admin.cars.store'), $this->carData())->assertRedirect(route('admin.cars'));
        $car = Car::where('name', 'Toyota Alphard Baru')->firstOrFail();
        $this->get(route('catalog', ['city' => 'bali']))->assertOk()->assertSee($car->name);
        $this->get(route('home'))->assertSee($car->name);
        $this->get(route('detail', $car->id))->assertOk();
        $this->put(route('admin.cars.update', $car), $this->carData(['price' => 2900000]))->assertRedirect(route('admin.cars'));
        $this->get(route('detail', $car->id))->assertSee('Rp 2.900.000');
        $this->get(route('admin.cars', ['q' => 'Alphard']))->assertOk()->assertSee($car->name)->assertDontSee('BMW 530i Opulence');
    }

    public function test_deactivation_preserves_old_bookings_and_rejects_new_reservations(): void
    {
        $booking = Booking::factory()->create();
        $car = Car::findOrFail('porsche-718-cayman');
        $this->actingAs(User::factory()->admin()->create());
        $this->put(route('admin.cars.update', $car), $this->carData(['is_active' => 0]))->assertRedirect();
        $this->get(route('detail', $car->id))->assertNotFound();
        $this->get(route('catalog'))->assertDontSee('Porsche 718 Cayman');
        $this->post(route('reserve'), $booking->reservation)->assertSessionHasErrors('car_id');
        $this->withSession(['reservation' => $booking->reservation])->get(route('checkout'))->assertRedirect(route('catalog'));
        $this->withSession(['rental_owner' => $booking->owner_token])->get(route('confirmation', $booking->code))->assertOk()->assertSee($booking->code);
        $this->get(route('orders'))->assertOk()->assertSee($booking->code);
        $this->get(route('admin.booking', $booking))->assertOk();
    }

    public function test_car_validation_and_safe_image_upload(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->post(route('admin.cars.store'), $this->carData(['price' => -1, 'image' => 'javascript:alert(1)', 'city' => 'unknown']))->assertSessionHasErrors(['price', 'image', 'city']);
        $this->assertDatabaseCount('cars', 6);
        Storage::fake('public');
        $image = UploadedFile::fake()->createWithContent('mobil.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aF9sAAAAASUVORK5CYII='));
        $this->post(route('admin.cars.store'), $this->carData(['image' => null, 'image_upload' => $image]))->assertRedirect();
        $car = Car::where('name', 'Toyota Alphard Baru')->firstOrFail();
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $car->image));
        $this->post(route('admin.cars.store'), $this->carData(['image_upload' => UploadedFile::fake()->create('script.php', 2, 'application/x-httpd-php')]))->assertSessionHasErrors('image_upload');
    }

    public function test_admin_can_review_filter_and_update_reservations_without_changing_customer_or_quote(): void
    {
        $booking = Booking::factory()->create();
        $customer = $booking->customer;
        $quote = $booking->quote;
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.bookings', ['q' => $booking->code, 'status' => 'demo']))->assertOk()->assertSee($booking->code);
        $this->get(route('admin.booking', $booking))->assertOk()->assertSee($customer['email']);
        $this->put(route('admin.booking.update', $booking), ['status' => 'confirmed', 'admin_notes' => 'Jadwal telah dikonfirmasi.', 'customer' => [], 'quote' => ['total' => 1]])->assertRedirect(route('admin.booking', $booking));
        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame($quote, $booking->quote);
        $this->assertSame($customer, $booking->customer);
        $this->assertSame('Jadwal telah dikonfirmasi.', $booking->admin_notes);
        $this->withSession(['rental_owner' => $booking->owner_token])->get(route('orders'))->assertSee('Dikonfirmasi')->assertDontSee('Jadwal telah dikonfirmasi.');
        $this->put(route('admin.booking.update', $booking), ['status' => 'invalid'])->assertSessionHasErrors('status');
        $this->actingAs(User::factory()->create())->put(route('admin.booking.update', $booking), ['status' => 'cancelled'])->assertForbidden();
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_admin_can_change_password_only_with_the_current_password(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $this->get(route('admin.account'))->assertOk()->assertSee($admin->email);
        $data = ['current_password' => 'wrong', 'password' => 'NewSecurePassword42!', 'password_confirmation' => 'NewSecurePassword42!'];
        $this->put(route('admin.password.update'), $data)->assertSessionHasErrors('current_password');
        $data['current_password'] = 'password';
        $this->put(route('admin.password.update'), $data)->assertRedirect(route('admin.account'));
        $this->assertTrue(Hash::check('NewSecurePassword42!', $admin->fresh()->password));
        $this->get(route('admin.account'))->assertOk();
    }

    public function test_seeding_does_not_overwrite_inventory_changes(): void
    {
        $car = Car::findOrFail('porsche-718-cayman');
        $car->update(['price' => 4000000, 'is_active' => false]);
        $this->seed(CarSeeder::class);
        $this->assertSame(4000000, $car->fresh()->price);
        $this->assertFalse($car->fresh()->is_active);
        $this->assertDatabaseCount('cars', 6);
    }

    public function test_admin_creation_command_creates_a_hashed_password_and_preserves_existing_accounts(): void
    {
        $this->artisan('admin:create', ['email' => 'admin-test@example.com'])->assertExitCode(0);
        $admin = User::where('email', 'admin-test@example.com')->firstOrFail();
        $this->assertTrue($admin->is_admin);
        $this->assertStringStartsWith('$2y$', $admin->getRawOriginal('password'));
        $original = $admin->password;
        $this->artisan('admin:create', ['email' => $admin->email])->assertExitCode(1);
        $this->assertSame($original, $admin->fresh()->password);
    }
}
