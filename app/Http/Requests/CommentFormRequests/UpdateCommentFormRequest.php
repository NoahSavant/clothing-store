<?php

namespace App\Http\Requests\CommentFormRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentFormRequest extends FormRequest
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
    public function rules()
    {
        return [
            'content' => 'required|string',
        ];
    }
}
