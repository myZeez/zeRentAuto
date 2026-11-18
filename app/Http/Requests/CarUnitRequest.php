<?php

namespace App\Http\Requests;

use App\Models\CarUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('car_units')->ignore($this->route('unit'))],
            'color' => ['required', 'string', 'max:40'],
            'color_hex' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'registration' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(array_keys(CarUnit::STATUSES))],
            'image' => ['nullable', 'string', 'max:2048', 'regex:~^(https?://[^\s]+|/(?!/)[a-zA-Z0-9/_\.\-]+)$~'],
            'image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
