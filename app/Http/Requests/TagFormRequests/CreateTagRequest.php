<?php

namespace App\Http\Requests\TagFormRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTagRequest extends FormRequest
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
            'id' => 'sometimes|required',
            'parent_id' => 'required_with:id|not_in:null',
            'parent_type' => 'required_with:id|not_in:null',
            'name' => 'required_without:id|not_in:null',
            'color' => 'required_without:id|not_in:null',
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

