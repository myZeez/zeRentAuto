<?php

use App\Services\BookingPhone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('phone_hash', 64)->nullable()->index();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('returned_at')->nullable();
        });
        foreach (DB::table('bookings')->select('id', 'customer')->orderBy('id')->cursor() as $booking) {
            $customer = json_decode(Crypt::decryptString($booking->customer), true, flags: JSON_THROW_ON_ERROR);
            DB::table('bookings')->where('id', $booking->id)->update(['phone_hash' => BookingPhone::hash($customer['phone'] ?? '')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['phone_hash']);
            $table->dropColumn(['phone_hash', 'starts_at', 'due_at', 'returned_at']);
        });
    }
};
