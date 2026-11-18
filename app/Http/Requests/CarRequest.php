<?php

namespace App\Http\Requests;

use App\Models\Car;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:1', 'max:10000000'],
            'category' => ['required', 'string', 'max:80'],
            'specs' => ['required', 'string', 'max:200'],
            'seats' => ['required', 'integer', 'min:1', 'max:20'],
            'year' => ['required', 'integer', 'min:1990', 'max:'.(now()->year + 1)],
            'city' => ['required', Rule::in(array_keys(Car::CITIES))],
            'type' => ['required', Rule::in(array_keys(Car::TYPES))],
            'image' => ['nullable', 'required_without:image_upload', 'string', 'max:2048', 'regex:~^(https?://[^\s]+|/(?!/)[a-zA-Z0-9/_\.\-]+)$~'],
            'image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
