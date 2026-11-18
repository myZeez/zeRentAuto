<?php

namespace App\Models;

use Database\Factories\CarUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarUnit extends Model
{
    /** @use HasFactory<CarUnitFactory> */
    use HasFactory;

    protected $fillable = ['car_id', 'code', 'color', 'color_hex', 'registration', 'image', 'status'];

    public const STATUSES = ['ready' => 'Siap disewakan', 'maintenance' => 'Perawatan', 'inactive' => 'Nonaktif'];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'unit_id');
    }

    public function snapshot(): array
    {
        return ['unit_id' => $this->id, 'unit_code' => $this->code, 'unit_color' => $this->color, 'unit_image' => $this->image];
    }
}
