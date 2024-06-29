<?php

namespace App\Http\Requests\VariantFormRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVariantFormRequest extends FormRequest
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
            'size' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
            'status' => 'required',
            'original_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];
    }

}
