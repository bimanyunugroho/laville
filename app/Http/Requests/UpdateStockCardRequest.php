<?php

namespace App\Http\Requests;

use App\Enums\StatusRunningCurrentStockEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockCardRequest extends FormRequest
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
            'beginning_balance' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'in_balance' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'out_balance' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'ending_balance' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'month' => [$this->isUpdate() ? 'required' : 'sometimes', 'integer', 'between:1,12'],
            'year' => [$this->isUpdate() ? 'required' : 'sometimes', 'integer', 'digits:4'],
            'status_running' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::in(StatusRunningCurrentStockEnum::values())],
            'stockCardDetails' => [$this->isUpdate() ? 'required' : 'sometimes', 'array', 'min:1'],
            'stockCardDetails.*.stock_card_id' => [$this->isUpdate() ? 'required' : 'sometimes'],
            'stockCardDetails.*.reference_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'stockCardDetails.*.reference_type' => [$this->isUpdate() ? 'required' : 'sometimes', 'string'],
            'stockCardDetails.*.reference_status' => [$this->isUpdate() ? 'required' : 'sometimes', 'string'],
            'stockCardDetails.*.unit_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:units,id'],
            'stockCardDetails.*.transaction_date' => [$this->isUpdate() ? 'required' : 'sometimes'],
            'stockCardDetails.*.movement_type' => [$this->isUpdate() ? 'required' : 'sometimes', 'string'],
            'stockCardDetails.*.quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'stockCardDetails.*.base_quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'stockCardDetails.*.balance_quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'stockCardDetails.*.balance_base_quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'stockCardDetails.*.notes' => [$this->isUpdate() ? 'required' : 'sometimes', 'string']
        ];
    }

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }
}
