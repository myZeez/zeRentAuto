<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Car;
use App\Models\CarUnit;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class UnitAvailability
{
    public function window(array $reservation, ?CarbonImmutable $startsAt = null, ?CarbonImmutable $dueAt = null): array
    {
        $start = $startsAt ?? CarbonImmutable::parse($reservation['start_date'].' '.($reservation['start_time'] ?? '00:00'), 'Asia/Jakarta');
        $end = $dueAt ?? CarbonImmutable::parse($reservation['end_date'].' '.($reservation['end_time'] ?? '00:00'), 'Asia/Jakarta');
        if (! $dueAt && empty($reservation['end_time'])) {
            $end = $end->addDay();
        }

        return [$start, $end];
    }

    public function blockingBookings(CarUnit $unit, ?int $except = null, ?Collection $legacy = null): Collection
    {
        $bookings = $unit->relationLoaded('bookings') ? $unit->bookings : $unit->bookings()->whereIn('status', Booking::BLOCKING_STATUSES)->get();
        $legacy ??= Booking::whereNull('unit_id')->where('reservation->car_id', $unit->car_id)->whereIn('status', Booking::BLOCKING_STATUSES)->get();

        return $bookings->concat($legacy)->filter(fn (Booking $booking) => in_array($booking->status, Booking::BLOCKING_STATUSES, true) && $booking->id !== $except);
    }

    public function overlaps(Booking $booking, array $reservation, ?CarbonImmutable $startsAt = null, ?CarbonImmutable $dueAt = null): bool
    {
        if ($booking->status === 'active' && ($booking->isOverdue() || (! $booking->due_at && empty($booking->reservation['end_time'])))) {
            return true;
        }
        [$start, $end] = $this->window($reservation, $startsAt, $dueAt);
        [$occupiedStart, $occupiedEnd] = $this->window($booking->reservation, $booking->starts_at, $booking->due_at);

        return $start->isBefore($occupiedEnd) && $end->isAfter($occupiedStart);
    }

    public function available(CarUnit $unit, array $reservation, ?int $except = null, ?CarbonImmutable $startsAt = null, ?CarbonImmutable $dueAt = null): bool
    {
        return $unit->status === 'ready' && ! $this->blockingBookings($unit, $except)->contains(fn (Booking $booking) => $this->overlaps($booking, $reservation, $startsAt, $dueAt));
    }

    public function assertAvailable(CarUnit $unit, array $reservation, ?int $except = null, ?CarbonImmutable $startsAt = null, ?CarbonImmutable $dueAt = null): void
    {
        if (! $this->available($unit, $reservation, $except, $startsAt, $dueAt)) {
            throw ValidationException::withMessages(['unit_id' => 'Unit ini tidak tersedia untuk jadwal tersebut. Pilih unit lain atau ubah jadwal sewa.']);
        }
    }

    public function inventory(Car $car, ?array $reservation = null): array
    {
        $current = CarbonImmutable::now('Asia/Jakarta');
        $reservation ??= ['start_date' => $current->toDateString(), 'end_date' => $current->addMinute()->toDateString(), 'start_time' => $current->format('H:i'), 'end_time' => $current->addMinute()->format('H:i')];
        $legacy = Booking::whereNull('unit_id')->where('reservation->car_id', $car->id)->whereIn('status', Booking::BLOCKING_STATUSES)->get();
        $collection = $car->relationLoaded('units') ? $car->units : $car->units()->with(['bookings' => fn ($query) => $query->whereIn('status', Booking::BLOCKING_STATUSES)])->orderBy('id')->get();
        $units = $collection->where('status', '!=', 'inactive')->map(function (CarUnit $unit) use ($reservation, $legacy): array {
            $bookings = $this->blockingBookings($unit, legacy: $legacy);
            $rented = $bookings->firstWhere('status', 'active');
            $conflicts = $bookings->filter(fn ($booking) => $this->overlaps($booking, $reservation));
            $conflict = $rented && $conflicts->contains('id', $rented->id) ? $rented : $conflicts->sortByDesc(fn ($booking) => $this->window($booking->reservation, $booking->starts_at, $booking->due_at)[1]->timestamp)->first();
            $available = $unit->status === 'ready' && $conflicts->isEmpty();
            $cause = $unit->status === 'maintenance' ? 'maintenance' : ($conflict?->isOverdue() ? 'overdue' : ($conflict ? ($conflict->status === 'active' ? 'rented' : 'reserved') : 'ready'));
            $busy = $conflict ?? $rented;
            $until = $busy ? ($busy->due_at?->translatedFormat('d M Y, H:i').' WIB') : null;
            if ($busy && ! $busy->due_at) {
                $until = CarbonImmutable::parse($busy->reservation['end_date'])->translatedFormat('d M Y').(! empty($busy->reservation['end_time']) ? ', '.$busy->reservation['end_time'].' WIB' : ' · jam menunggu konfirmasi');
            }

            return ['id' => $unit->id, 'code' => $unit->code, 'color' => $unit->color, 'color_hex' => $unit->color_hex, 'image' => $unit->image, 'available' => $available, 'rented' => (bool) $rented, 'cause' => $cause, 'label' => ['ready' => 'Tersedia', 'maintenance' => 'Perawatan', 'overdue' => 'Menunggu pengembalian', 'rented' => 'Sedang disewa', 'reserved' => 'Sudah dipesan'][$cause], 'until' => $until, 'overdue' => (bool) $rented?->isOverdue()];
        });

        return ['units' => $units->values()->all(), 'total' => $units->count(), 'available' => $units->where('available', true)->count(), 'rented' => $units->where('rented', true)->count()];
    }
}
