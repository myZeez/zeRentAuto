<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarUnitRequest;
use App\Models\Car;
use App\Models\CarUnit;
use App\Services\UnitAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CarUnitController extends Controller
{
    public function index(Car $car, UnitAvailability $availability): View
    {
        return view('admin.units', ['car' => $car, 'units' => $car->units()->orderBy('id')->get(), 'inventory' => $availability->inventory($car)]);
    }

    public function store(CarUnitRequest $request, Car $car): RedirectResponse
    {
        $data = $this->data($request);
        $data['code'] = ($data['code'] ?? null) ?: 'ZE-'.Str::upper(Str::random(8));
        $car->units()->create($data);

        return to_route('admin.units', $car)->with('status', 'Unit berhasil ditambahkan.');
    }

    public function edit(Car $car, CarUnit $unit): View
    {
        abort_unless($unit->car_id === $car->id, 404);

        return view('admin.unit-form', compact('car', 'unit'));
    }

    public function update(CarUnitRequest $request, Car $car, CarUnit $unit): RedirectResponse
    {
        abort_unless($unit->car_id === $car->id, 404);
        $data = $this->data($request);
        $data['code'] = ($data['code'] ?? null) ?: $unit->code;
        $unit->update($data);

        return to_route('admin.units', $car)->with('status', 'Unit berhasil diperbarui.');
    }

    private function data(CarUnitRequest $request): array
    {
        $data = $request->safe()->except('image_upload');
        if ($request->hasFile('image_upload')) {
            $data['image'] = '/storage/'.$request->file('image_upload')->store('units', 'public');
        }

        return $data;
    }
}
