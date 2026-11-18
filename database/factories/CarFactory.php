<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->slug(),
            'name' => 'Mobil '.fake()->word(),
            'image' => '/images/hero-mercedes.png',
            'price' => 1500000,
            'category' => 'Executive Sedan',
            'specs' => 'Otomatis · Kabin premium',
            'seats' => 5,
            'year' => 2024,
            'city' => 'jakarta',
            'type' => 'sedan',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 10,
        ];
    }
}
