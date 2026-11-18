<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingLookupRequest;
use App\Models\Booking;
use App\Models\Car;
use App\Services\BookingPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class BookingStatusController extends Controller
{
    public function index(Request $request): Response
    {
        $access = $request->session()->get('booking_access');
        if ($access && (! is_array($access) || ! isset($access['booking_id'], $access['code'], $access['phone_hash'], $access['expires_at'], $access['last_digits']) || $access['expires_at'] <= now()->timestamp)) {
            $request->session()->forget('booking_access');
            $request->session()->put('booking_lookup_required', true);
            $access = null;
        }
        $owner = $request->session()->get('booking_lookup_required') ? null : $request->session()->get('rental_owner');
        $query = Booking::query();
        if ($access) {
            $query->whereKey($access['booking_id'])->where('code', $access['code'])->where('phone_hash', $access['phone_hash']);
        } elseif ($owner) {
            $query->where('owner_token', $owner);
        } else {
            $query->whereRaw('1 = 0');
        }

        return response()->view('pages.booking-status', [
            'bookings' => (clone $query)->latest()->paginate(8),
            'stats' => ['total' => (clone $query)->count(), 'active' => (clone $query)->where('status', 'active')->count(), 'completed' => (clone $query)->where('status', 'completed')->count()],
            'cars' => Car::all()->keyBy('id'),
            'access' => $access,
            'hasAccess' => (bool) ($owner || $access),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function lookup(BookingLookupRequest $request): RedirectResponse
    {
        $request->session()->forget('booking_access');
        $hash = BookingPhone::hash($request->validated('phone'));
        $key = 'booking-lookup:'.$hash.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['code' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.']);
        }
        $booking = Booking::where('phone_hash', $hash)->where('code', $request->validated('code'))->first();
        if (! $booking) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['code' => 'Nomor telepon dan kode booking tidak cocok. Periksa kembali keduanya.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('booking_access', ['booking_id' => $booking->id, 'code' => $booking->code, 'phone_hash' => $hash, 'last_digits' => substr(BookingPhone::normalize($request->validated('phone')), -4), 'expires_at' => now()->addMinutes(30)->timestamp]);

        return to_route('booking-status');
    }

    public function close(Request $request): RedirectResponse
    {
        $request->session()->forget(['booking_access', 'rental_owner', 'booking_lookup_required']);
        $request->session()->regenerate();

        return to_route('booking-status');
    }
}
