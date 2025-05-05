<?php

namespace App\Http\Requests;

use App\Enums\StatusNotesStockOutEnum;
use App\Enums\StatusStockOutEnum;
use App\Models\StockOut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockOutRequest extends FormRequest
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
            'stock_out_number' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::unique(StockOut::class)->ignore(request()->route('stock_out')->id)],
            'supplier_id' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'exists:suppliers,id'],
            'user_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:users,id'],
            'user_ack_id' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'exists:users,id'],
            'out_date' => [$this->isUpdate() ? 'required' : 'sometimes', 'date_format:Y-m-d H:i:s'],
            'ack_date' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'date_format:Y-m-d H:i:s'],
            'user_reject_id' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'exists:users,id'],
            'reject_date' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'date_format:Y-m-d H:i:s'],
            'subtotal' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'tax' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'discount' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'total_net' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'status_notes' => [$this->isUpdate() ? 'required' : 'sometimes', Rule::in(StatusNotesStockOutEnum::values())],
            'status' => [$this->isUpdate() ? 'required' : 'sometimes', Rule::in(StatusStockOutEnum::values())],
            'notes' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'string'],
            'is_active' => [$this->isUpdate() ? 'required' : 'sometimes', 'boolean'],
            'details' => [$this->isUpdate() ? 'required' : 'sometimes', 'array', 'min:1'],
            'details.*.product_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:products,id'],
            'details.*.unit_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:units,id'],
            'details.*.quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.base_quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.price' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'details.*.subtotal' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'details.*.received_quantity' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'numeric', 'min:0'],
            'details.*.received_base_quantity' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'numeric', 'min:0'],
            'details.*.notes_detail' => [$this->isUpdate() ? 'required' : 'sometimes', 'string'],
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
            'details.*.quantity' => 'quantity',
            'details.*.base_quantity' => 'base quantity',
            'details.*.price' => 'price',
            'details.*.subtotal' => 'subtotal',
            'details.*.received_quantity' => 'received quantity',
            'details.*.received_base_quantity' => 'received base quantity',
            'details.*.notes_detail' => 'notes detail',
        ];
    }

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }
}
