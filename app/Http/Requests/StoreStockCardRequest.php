<?php

namespace App\Http\Requests;

use App\Enums\StatusRunningCurrentStockEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockCardRequest extends FormRequest
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
            'product_id' => ['required', 'exists:products,id'],
            'beginning_balance' => ['required', 'numeric'],
            'in_balance' => ['required', 'numeric'],
            'out_balance' => ['required', 'numeric'],
            'ending_balance' => ['required', 'numeric'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
            'status_running' => ['required', 'string', Rule::in(StatusRunningCurrentStockEnum::values())],
            'stockCardDetails' => ['required', 'array', 'min:1'],
            'stockCardDetails.*.stock_card_id' => ['required'],
            'stockCardDetails.*.reference_id' => ['required', 'numeric'],
            'stockCardDetails.*.reference_type' => ['required', 'string'],
            'stockCardDetails.*.reference_status' => ['required', 'string'],
            'stockCardDetails.*.unit_id' => ['required', 'exists:units,id'],
            'stockCardDetails.*.transaction_date' => ['required'],
            'stockCardDetails.*.movement_type' => ['required', 'string'],
            'stockCardDetails.*.quantity' => ['required', 'numeric'],
            'stockCardDetails.*.base_quantity' => ['required', 'numeric'],
            'stockCardDetails.*.balance_quantity' => ['required', 'numeric'],
            'stockCardDetails.*.balance_base_quantity' => ['required', 'numeric'],
            'stockCardDetails.*.notes' => ['required', 'string']
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'stockCardDetails.*.stock_card_id' => 'Stock Card ID',
            'stockCardDetails.*.reference_id' => 'Reference ID',
            'stockCardDetails.*.reference_type' => 'Reference Type',
            'stockCardDetails.*.reference_status' => 'Reference Status',
            'stockCardDetails.*.unit_id' => 'Unit ID',
            'stockCardDetails.*.transaction_date' => 'Transaction Date',
            'stockCardDetails.*.movement_type' =>  'Movement Type',
            'stockCardDetails.*.quantity' => 'Quantity',
            'stockCardDetails.*.base_quantity' => 'Base Quantity',
            'stockCardDetails.*.balance_quantity' => 'Balance Quantity',
            'stockCardDetails.*.balance_base_quantity' => 'Balance Base Quantity',
            'stockCardDetails.*.notes' => 'Notes'
        ];
    }
}
