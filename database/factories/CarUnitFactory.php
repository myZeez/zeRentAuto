<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarUnitFactory extends Factory
{
    public function definition(): array
    {
        return ['car_id' => Car::factory(), 'code' => fake()->unique()->bothify('ZE-####-???'), 'color' => 'Putih', 'color_hex' => '#f5f5ef', 'status' => 'ready'];
    }
}
