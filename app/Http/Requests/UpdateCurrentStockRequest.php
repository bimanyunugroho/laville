<?php

namespace App\Http\Requests;

use App\Enums\StatusRunningCurrentStockEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurrentStockRequest extends FormRequest
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
            'product_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:products,id'],
            'unit_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:units,id'],
            'quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'base_quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'month' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'between:1,12'],
            'year' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'digits:4'],
            'status_running' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::in(StatusRunningCurrentStockEnum::values())]
        ];
    }

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }
}
