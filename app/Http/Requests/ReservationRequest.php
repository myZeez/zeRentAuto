<?php

namespace App\Http\Requests;

use App\Models\CarUnit;
use App\Services\RentalQuote;
use App\Services\UnitAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'car_id' => ['required', Rule::in(app(RentalQuote::class)->cars()->keys()->all())],
            'unit_id' => ['required', 'integer', Rule::exists('car_units', 'id')->where('car_id', $this->input('car_id'))->where('status', 'ready')],
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after:start_date', 'before_or_equal:'.now()->addYear()->toDateString()],
            'start_time' => ['nullable', 'date_format:H:i', 'required_with:end_time'],
            'end_time' => ['nullable', 'date_format:H:i', 'required_with:start_time'],
            'location' => ['required', Rule::in(['Senopati Suites Showroom', 'Bandara Soekarno-Hatta', 'Hotel / Domisili Jakarta'])],
            'service' => ['required', Rule::in(['self', 'driver'])],
            'insurance' => ['sometimes', 'boolean'], 'valet' => ['sometimes', 'boolean'], 'fuel' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['unit_id.required' => 'Pilih unit kendaraan terlebih dahulu.', 'unit_id.exists' => 'Pilih unit yang tersedia untuk tipe mobil ini.', 'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.', 'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.', 'end_date.before_or_equal' => 'Pemesanan maksimal satu tahun ke depan.', '*.required' => 'Kolom :attribute wajib diisi.'];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if ($this->filled('start_time') && CarbonImmutable::parse($this->input('start_date').' '.$this->input('start_time'), 'Asia/Jakarta')->isPast()) {
                $validator->errors()->add('start_time', 'Jam pengambilan sudah lewat. Pilih waktu pengambilan berikutnya.');

                return;
            }
            $unit = CarUnit::find($this->input('unit_id'));
            if (! app(UnitAvailability::class)->available($unit, $this->only('start_date', 'end_date', 'start_time', 'end_time'))) {
                $validator->errors()->add('unit_id', 'Unit sudah dipesan pada jadwal ini. Pilih unit lain atau ubah tanggal dan jam sewa.');
            }
        }];
    }
}
