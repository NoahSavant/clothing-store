<?php

namespace App\Http\Requests\ProductFormRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductFormRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'status' => 'required',
            'note' => 'nullable|string',
        ];
    }
}
