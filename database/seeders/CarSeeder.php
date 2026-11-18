<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cars = json_decode(file_get_contents(resource_path('data/cars.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($cars as $index => $car) {
            Car::firstOrCreate(['id' => $car['id']], $car + ['sort_order' => $index, 'is_active' => true, 'is_featured' => in_array($car['id'], ['mercedes-benz-e-300', 'bmw-530i-opulence', 'toyota-vellfire-lounge', 'porsche-718-cayman'])]);
        }
        $this->call(CarUnitSeeder::class);
    }
}
