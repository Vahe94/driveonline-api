<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'vin' => 'nullable|string|max:64',
            'price'       => 'required|integer|min:1',
            'photos'       => 'nullable|array|max:10',
            'photos.*' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:3072',
        ];
    }
}
