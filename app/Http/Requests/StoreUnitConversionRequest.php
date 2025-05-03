<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreUnitConversionRequest extends FormRequest
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
            'product_id'    => ['required', 'exists:products,id'],
            'from_unit_id'  => [
                'required',
                'exists:units,id',
                function($attribute, $value, $fail) {
                    $productId = request('product_id');
                    $product = Product::find($productId);
                    if ($product && $value != $product->default_unit_id) {
                        $fail('Target unit must be the same as the product\'s default unit.');
                    }
                }
            ],
            'to_unit_id'  => ['required', 'exists:units,id'],
            'conversion_factor' => ['required', 'numeric', 'min:0']
        ];
    }
}
