<?php

namespace App\Http\Requests\DiscountFormRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDiscountRequest extends FormRequest
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
            'type' => 'required|integer',
            'subject' => 'required|integer',
            'condition' => 'required|integer',
            'value' => 'required',
            'max_price' => 'required|integer|min:0',
            'status' => 'required|integer',
            'started_at' => 'required|string',
            'ended_at' => 'required|string',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'parent_id.required_with' => 'Unable to determine object to assign tag to.',
            'parent_type.required_with' => 'Unable to determine object to assign tag to.',
            'name.required_without' => 'The name field is required.',
            'color.required_without' => 'The color field is required.',
        ];
    }
}

