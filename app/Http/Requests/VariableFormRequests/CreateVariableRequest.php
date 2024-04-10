<?php

namespace App\Http\Requests\VariableFormRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVariableRequest extends FormRequest
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
            'key' => 'required|string',
            'value' => 'required|string',
            'type' => 'required',
            'parent_id' => 'required',
            'parent_type' => 'required',
        ];
    }
}
