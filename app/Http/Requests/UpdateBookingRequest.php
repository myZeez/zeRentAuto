<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('unit_id')) {
            $this->merge(['unit_id' => $this->route('booking')?->unit_id]);
        }
        foreach (['start' => 'starts_at', 'due' => 'due_at'] as $prefix => $column) {
            if ($this->has($prefix.'_date') || $this->has($prefix.'_time')) {
                $date = $this->input($prefix.'_date');
                $time = $this->input($prefix.'_time');
                $this->merge([$column => is_string($date) && is_string($time) && $time !== '' ? $date.'T'.$time : null]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_keys(Booking::STATUSES))],
            'unit_id' => ['nullable', 'required_unless:status,cancelled', 'integer', Rule::exists('car_units', 'id')->where('car_id', $this->route('booking')?->reservation['car_id'] ?? '')],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['nullable', 'required_with:start_time', 'date_format:Y-m-d'],
            'due_date' => ['nullable', 'required_with:due_time', 'date_format:Y-m-d'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'starts_at' => ['nullable', 'required_with:due_at,returned_at', 'required_if:status,active,completed', 'date_format:Y-m-d\TH:i', Rule::when(in_array($this->input('status'), ['active', 'completed'], true), 'before_or_equal:now')],
            'due_at' => ['nullable', 'required_with:starts_at,returned_at', 'required_if:status,active,completed', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'returned_at' => ['nullable', 'required_if:status,completed', Rule::prohibitedIf($this->input('status') !== 'completed'), 'date_format:Y-m-d\TH:i', 'after_or_equal:starts_at', 'before_or_equal:now'],
        ];
    }

    public function attributes(): array
    {
        return ['start_date' => 'tanggal mulai sewa', 'due_date' => 'tanggal pengembalian', 'start_time' => 'jam mulai sewa', 'due_time' => 'jam pengembalian', 'starts_at' => 'waktu mulai sewa', 'due_at' => 'batas pengembalian', 'returned_at' => 'waktu pengembalian aktual'];
    }

    public function messages(): array
    {
        return [
            '*.required_if' => 'Isi :attribute untuk status yang dipilih.',
            '*.required_with' => 'Lengkapi :attribute bersama jadwal sewa.',
            '*.date_format' => 'Format :attribute tidak valid. Pilih tanggal atau jam melalui kolom yang tersedia.',
            'starts_at.before_or_equal' => 'Tanggal/jam mulai sewa masih di masa depan. Jika mobil sudah diserahkan, isi waktu serah terima sebenarnya pada Tanggal mulai dan Jam mulai (WIB). Jika belum diserahkan, gunakan Dikonfirmasi.',
            'due_at.after' => 'Batas pengembalian harus setelah waktu mulai sewa.',
            'returned_at.after_or_equal' => 'Pengembalian aktual harus pada atau setelah waktu mulai sewa.',
            'returned_at.before_or_equal' => 'Waktu pengembalian aktual belum terjadi. Kosongkan kolom ini sampai mobil benar-benar kembali.',
            'returned_at.prohibited' => 'Waktu pengembalian aktual hanya diisi saat status Selesai.',
        ];
    }
}
