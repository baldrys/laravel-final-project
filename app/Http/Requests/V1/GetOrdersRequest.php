<?php

namespace App\Http\Requests\V1;

use App\Support\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetOrdersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => ['required', Rule::in([
                OrderStatus::Canceled,
                OrderStatus::Placed,
                OrderStatus::Approved,
                OrderStatus::Shipped,
                OrderStatus::Received,
            ])],
            'min_total_price' => 'required|Numeric|min:1',
            'max_total_price' => 'required|Numeric|min:1',
        ];
    }
}
