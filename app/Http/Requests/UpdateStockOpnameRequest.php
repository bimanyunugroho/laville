<?php

namespace App\Http\Requests;

use App\Enums\StatusStockOpnameEnum;
use App\Models\StockOpname;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockOpnameRequest extends FormRequest
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
            'so_number' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::unique(StockOpname::class)->ignore(request()->route('stock_opname')->id)],
            'month' => [$this->isUpdate() ? 'required' : 'sometimes', 'integer', 'between:1,12'],
            'year' => [$this->isUpdate() ? 'required' : 'sometimes', 'integer', 'digits:4'],
            'user_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:users,id'],
            'so_date' => [$this->isUpdate() ? 'required' : 'sometimes', 'date_format:Y-m-d H:i:s'],
            'user_validator_id' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'exists:users,id'],
            'validator_date' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'date_format:Y-m-d H:i:s'],
            'user_ack_id' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'exists:users,id'],
            'ack_date' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'date_format:Y-m-d H:i:s'],
            'user_reject_id' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'exists:users,id'],
            'reject_date' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'date_format:Y-m-d H:i:s'],
            'subtotal' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'tax' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'discount' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'total_net' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'status' => [$this->isUpdate() ? 'required' : 'sometimes', Rule::in(StatusStockOpnameEnum::values())],
            'notes' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'string'],
            'is_locked' => [$this->isUpdate() ? 'required' : 'sometimes', 'boolean'],
            'is_active' => [$this->isUpdate() ? 'required' : 'sometimes', 'boolean'],
            'details' => [$this->isUpdate() ? 'required' : 'sometimes', 'array', 'min:1'],
            'details.*.product_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:products,id'],
            'details.*.unit_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:units,id'],
            'details.*.system_stock' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.system_stock_base' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.physical_stock' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.physical_stock_base' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.difference_stock' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.difference_stock_base' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.price' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.subtotal' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.status' => [$this->isUpdate() ? 'required' : 'sometimes', 'string'],
            'details.*.notes' => [$this->isUpdate() ? 'required' : 'sometimes', 'string'],
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
            'details.*.product_id' => 'product',
            'details.*.unit_id' => 'unit',
            'details.*.system_stock' => 'system stock',
            'details.*.system_stock_base' => 'system stock base',
            'details.*.physical_stock' => 'physical stock',
            'details.*.physical_stock_base' => 'physical stock base',
            'details.*.difference_stock' => 'difference stock base',
            'details.*.difference_stock_base' => 'difference stock base',
            'details.*.price' => 'price',
            'details.*.subtotal' => 'subtotal',
            'details.*.status' => 'status',
            'details.*.notes' => 'notes',
        ];
    }

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }
}
