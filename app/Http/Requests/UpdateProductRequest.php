<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'code' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', 'max:5', Rule::unique(Product::class)->ignore(request()->route('product')->id)],
            'name' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::unique(Product::class)->ignore(request()->route('product')->id)],
            'variant_name' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::unique(Product::class)->ignore(request()->route('product')->id)],
            'default_unit_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:units,id'],
            'purchase_price' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'selling_price' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'description' => [$this->isUpdate() ? 'required' : 'sometimes', 'string'],
            'is_active' => [$this->isUpdate() ? 'required' : 'sometimes', 'boolean']
        ];
    }

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }
}
