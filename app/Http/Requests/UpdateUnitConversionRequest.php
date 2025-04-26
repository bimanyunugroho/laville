<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitConversionRequest extends FormRequest
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
            'product_id'    => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:products,id'],
            'from_unit_id'  => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:units,id'],
            'to_unit_id'  => [
                $this->isUpdate() ? 'required' : 'sometimes',
                'exists:units,id',
                function($attribute, $value, $fail) {
                    $productId = request('product_id');
                    $product = Product::find($productId);
                    if ($product && $value != $product->default_unit_id) {
                        $fail('Target unit must be the same as the product\'s default unit.');
                    }
                }
            ],
            'conversion_factor' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'min:0']
        ];
    }

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }
}
