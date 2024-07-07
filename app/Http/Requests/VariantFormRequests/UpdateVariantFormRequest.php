<?php

namespace App\Http\Requests\VariantFormRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVariantFormRequest extends FormRequest
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
            'product_size_id' => 'required',
            'product_color_id' => 'required',
            'status' => 'required',
            'original_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_limit' => 'required|integer|min:0',
        ];
    }
}
