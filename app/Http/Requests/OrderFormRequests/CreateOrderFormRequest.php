<?php

namespace App\Http\Requests\OrderFormRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderFormRequest extends FormRequest
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
            'total_price' => 'required',
            'transportation_method' => 'required',
            'product_price' => 'required',
            'transport_price' => 'required',
            'product_discount' => 'required',
            'transport_discount' => 'required',
            'payment_method' => 'required',
            'phonenumber' => 'required',
            'address' => 'required',
            'orderItems' => 'required|array|min:1'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'total_price.required' => 'Tổng giá là bắt buộc.',
            'transportation_method.required' => 'Phương thức vận chuyển là bắt buộc.',
            'product_price.required' => 'Giá sản phẩm là bắt buộc.',
            'transport_price.required' => 'Giá vận chuyển là bắt buộc.',
            'product_discount.required' => 'Chiết khấu sản phẩm là bắt buộc.',
            'transport_discount.required' => 'Chiết khấu vận chuyển là bắt buộc.',
            'payment_method.required' => 'Phương thức thanh toán là bắt buộc.',
            'phonenumber.required' => 'Số điện thoại là bắt buộc.',
            'address.required' => 'Địa chỉ là bắt buộc.',
            'orderItems.required' => 'Phải có ít nhất một mặt hàng trong đơn hàng.',
            'orderItems.array' => 'OrderItems phải là một mảng.',
            'orderItems.min' => 'Phải có ít nhất một phần tử trong orderItems.',
        ];
    }
}
