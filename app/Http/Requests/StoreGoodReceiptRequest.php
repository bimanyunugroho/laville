<?php

namespace App\Http\Requests;

use App\Enums\StatusReceiptEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGoodReceiptRequest extends FormRequest
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
            'receipt_number' => ['required', 'string', 'unique:good_receipts,receipt_number'],
            'purchase_order_id' => ['required', 'string', 'exists:purchase_orders,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'user_id' => ['required', 'exists:users,id'],
            'receipt_date' => ['required', 'date'],
            'user_ack_id' => ['nullable', 'exists:users,id'],
            'ack_date' => ['nullable', 'date'],
            'user_reject_id' => ['nullable', 'exists:users,id'],
            'reject_date' => ['nullable', 'date'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'total_net' => ['required', 'numeric'],
            'status_receipt' => ['required', Rule::in(StatusReceiptEnum::values())],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.purchase_order_detail_id' => ['required', 'exists:purchase_order_details,id'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.unit_id' => ['required', 'exists:units,id'],
            'details.*.quantity' => ['required', 'numeric'],
            'details.*.base_quantity' => ['required', 'numeric'],
            'details.*.price' => ['required', 'numeric', 'min:0'],
            'details.*.subtotal' => ['required', 'numeric', 'min:0'],
            'details.*.received_quantity' => ['nullable', 'numeric', 'min:0'],
            'details.*.received_base_quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function attributes()
    {
        return [
            'details.*.purchase_order_detail_id' => 'purchase order detail',
            'details.*.product_id' => 'product',
            'details.*.unit_id' => 'unit',
            'details.*.quantity' => 'quantity',
            'details.*.base_quantity' => 'base quantity',
            'details.*.price' => 'price',
            'details.*.subtotal' => 'subtotal',
            'details.*.received_quantity' => 'received quantity',
            'details.*.received_base_quantity' => 'received base quantity',
        ];
    }
}
