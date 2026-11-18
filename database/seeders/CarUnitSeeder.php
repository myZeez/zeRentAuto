<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarUnitSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Car::orderBy('id')->get() as $index => $car) {
            if ($car->units()->exists()) {
                continue;
            }
            foreach ([['Silver', '#bfc4c9'], ['Hitam', '#262928']] as $number => [$color, $hex]) {
                $car->units()->create(['code' => 'ZE-'.strtoupper(substr(hash('sha256', $car->id), 0, 8)).'-'.($number + 1), 'color' => $color, 'color_hex' => $hex, 'status' => 'ready']);
            }
        }
    }
}
