<?php

use App\Models\Booking;
use App\Models\Car;
use App\Services\UnitAvailability;
use Database\Seeders\CarUnitSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_units', function (Blueprint $table): void {
            $table->id();
            $table->string('car_id', 128);
            $table->foreign('car_id')->references('id')->on('cars')->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('color', 40);
            $table->string('color_hex', 7)->default('#bfc4c9');
            $table->string('registration', 20)->nullable();
            $table->text('image')->nullable();
            $table->string('status', 20)->default('ready')->index();
            $table->timestamps();
        });
        Schema::table('bookings', function (Blueprint $table): void {
            $table->foreignId('unit_id')->nullable()->constrained('car_units')->restrictOnDelete();
        });
        (new CarUnitSeeder)->run();
        $availability = new UnitAvailability;
        foreach (Booking::orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'confirmed' THEN 1 ELSE 2 END")->orderBy('id')->get() as $booking) {
            $car = Car::find($booking->reservation['car_id']);
            if (! $car) {
                continue;
            }
            foreach ($car->units()->get() as $unit) {
                $conflict = $unit->bookings()->whereIn('status', Booking::BLOCKING_STATUSES)->get()->contains(fn ($other) => $availability->overlaps($other, $booking->reservation, $booking->starts_at, $booking->due_at));
                if (! in_array($booking->status, Booking::BLOCKING_STATUSES, true) || ! $conflict) {
                    DB::table('bookings')->where('id', $booking->id)->update(['unit_id' => $unit->id, 'reservation' => json_encode(array_merge($booking->reservation, $unit->snapshot()), JSON_THROW_ON_ERROR)]);
                    break;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('unit_id');
        });
        Schema::dropIfExists('car_units');
    }
};
