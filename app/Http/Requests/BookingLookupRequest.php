<?php

namespace App\Http\Requests;

use App\Services\BookingPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BookingLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->session()->forget('booking_access');
        $this->session()->put('booking_lookup_required', true);
        if (is_string($this->input('code'))) {
            $this->merge(['code' => Str::upper(trim($this->input('code')))]);
        }
    }

    public function rules(): array
    {
        return [
            'phone' => ['bail', 'required', 'string', 'max:20', 'regex:/^[0-9+()\s-]+$/', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! BookingPhone::normalize($value)) {
                    $fail('Masukkan nomor telepon yang valid.');
                }
            }],
            'code' => ['bail', 'required', 'string', 'max:30', 'regex:/^ATR-[A-Z0-9]{12}$/'],
        ];
    }

    public function messages(): array
    {
        return ['phone.required' => 'Masukkan nomor telepon saat booking.', 'phone.regex' => 'Masukkan nomor telepon yang valid.', 'code.required' => 'Masukkan kode booking untuk verifikasi.', 'code.regex' => 'Kode booking menggunakan format ATR- diikuti 12 karakter.'];
    }
}
