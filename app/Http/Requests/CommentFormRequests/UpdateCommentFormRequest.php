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
            'user_id' => 'required|integer',
            'commentmorph_id' => 'required_if:commentmorph_type|required_without:commentmorph_type|integer',
            'commentmorph_type' => 'required_if:commentmorph_id|required_without:commentmorph_id|integer',
            'content' => 'required|string',
            'hide' => 'boolean',
            'rate' => $this->input('commentmorph_type') != 2 ? 'required|integer' : '',
        ];
    }

    public function messages()
    {
        return [
            'commentmorph_id.required_if' => 'Đối tượng không xác định.',
            'commentmorph_type.required_if' => 'Đối tượng không xác định.',
            'rate.required' => 'Bạn chưa đánh giá.',
        ];
    }
}
