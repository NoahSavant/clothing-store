<?php

namespace App\Http\Requests\CommentFormRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommentFormRequest extends FormRequest
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
            'commentmorph_id' => 'required_without:commentmorph_type|integer',
            'commentmorph_type' => 'required_without:commentmorph_id|integer',
            'content' => 'required|string',
            'rate' => $this->input('commentmorph_type') != 2 ? 'required' : '',
        ];
    }

    public function messages()
    {
        return [
            'commentmorph_id.required_without' => 'Đối tượng không xác định.',
            'commentmorph_type.required_without' => 'Đối tượng không xác định.',
            'rate.required' => 'Bạn chưa đánh giá.',
        ];
    }
}

