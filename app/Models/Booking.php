<?php

namespace App\Models;

use App\Services\BookingPhone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'owner_token', 'reservation', 'quote', 'customer', 'status', 'admin_notes', 'starts_at', 'due_at', 'returned_at', 'unit_id'];

    public const STATUSES = ['demo' => 'Baru', 'confirmed' => 'Dikonfirmasi', 'active' => 'Sedang disewa', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];

    public const BLOCKING_STATUSES = ['demo', 'confirmed', 'active'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(CarUnit::class, 'unit_id');
    }

    protected function casts(): array
    {
        return ['reservation' => 'array', 'quote' => 'array', 'customer' => 'encrypted:array', 'starts_at' => 'immutable_datetime', 'due_at' => 'immutable_datetime', 'returned_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (Booking $booking): void {
            if ($booking->isDirty('customer')) {
                $booking->phone_hash = BookingPhone::hash($booking->customer['phone'] ?? '');
            }
        });
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active' && $this->due_at !== null && now()->isAfter($this->due_at);
    }

    public function returnedLate(): bool
    {
        return $this->status === 'completed' && $this->returned_at !== null && $this->due_at !== null && $this->returned_at->isAfter($this->due_at);
    }
}
