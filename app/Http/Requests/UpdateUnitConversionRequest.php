<?php

namespace App\Http\Requests;

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
            'product_id'    => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:product_id,id'],
            'from_unit_id'  => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:units,id'],
            'to_unit_id'  => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:units,id'],
            'conversion_factor' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'min:0']
        ];
    }

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }
}
