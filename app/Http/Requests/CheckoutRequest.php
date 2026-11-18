<?php

namespace App\Http\Requests;

use App\Services\BookingPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:150'], 'phone' => ['bail', 'required', 'string', 'regex:/^[0-9+()\\s-]{9,20}$/', function (string $attribute, mixed $value, \Closure $fail): void {
            if (! BookingPhone::normalize($value)) {
                $fail('Masukkan nomor telepon yang valid.');
            }
        }], 'notes' => ['nullable', 'string', 'max:1000'], 'payment_channel' => ['required', Rule::in(['va', 'cc', 'qris', 'invoice'])], 'terms' => ['accepted']];
    }

    public function messages(): array
    {
        return ['*.required' => 'Kolom :attribute wajib diisi.', 'email.email' => 'Masukkan alamat email yang valid.', 'phone.regex' => 'Masukkan nomor telepon yang valid.', 'terms.accepted' => 'Setujui ketentuan demo sebelum melanjutkan.'];
    }
}
