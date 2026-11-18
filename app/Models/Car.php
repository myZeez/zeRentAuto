<?php

namespace App\Models;

use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    /** @use HasFactory<CarFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'image', 'price', 'category', 'specs', 'seats', 'year', 'city', 'type', 'is_active', 'is_featured', 'sort_order'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'seats' => 'integer', 'year' => 'integer', 'is_active' => 'boolean', 'is_featured' => 'boolean'];
    }

    public const CITIES = ['jakarta' => 'Jakarta', 'bali' => 'Bali', 'surabaya' => 'Surabaya', 'bandung' => 'Bandung'];

    public const TYPES = ['sedan' => 'Sedan', 'suv' => 'SUV', 'mpv' => 'MPV', 'sports' => 'Sports car'];

    public function units(): HasMany
    {
        return $this->hasMany(CarUnit::class);
    }
}
