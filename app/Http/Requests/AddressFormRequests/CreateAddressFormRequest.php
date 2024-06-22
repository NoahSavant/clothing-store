<?php

namespace App\Http\Requests\AddressFormRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAddressFormRequest extends FormRequest
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
            'name' => 'required|string',
            'content' => 'required|string',
            'url' => 'required|string',
        ];
    }
}
