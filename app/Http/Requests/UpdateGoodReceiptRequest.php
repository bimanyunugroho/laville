<?php

namespace App\Http\Requests;

use App\Enums\StatusReceiptEnum;
use App\Models\GoodReceipt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGoodReceiptRequest extends FormRequest
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
            'receipt_number' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::unique(GoodReceipt::class)->ignore(request()->route('good_receipt')->id)],
            'purchase_order_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', 'exists:purchase_orders,id'],
            'supplier_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:suppliers,id'],
            'user_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:users,id'],
            'receipt_date' => [$this->isUpdate() ? 'required' : 'sometimes', 'date'],
            'user_ack_id' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'exists:users,id'],
            'ack_date' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'date'],
            'user_reject_id' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'exists:users,id'],
            'reject_date' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'date'],
            'subtotal' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'tax' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'discount' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'total_net' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'status_receipt' => [$this->isUpdate() ? 'required' : 'sometimes', Rule::in(StatusReceiptEnum::values())],
            'notes' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'string'],
            'is_active' => [$this->isUpdate() ? 'required' : 'sometimes', 'boolean'],
            'details' => [$this->isUpdate() ? 'required' : 'sometimes', 'array', 'min:1'],
            'details.*.purchase_order_detail_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:purchase_order_details,id'],
            'details.*.product_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:products,id'],
            'details.*.unit_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:units,id'],
            'details.*.quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.base_quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.price' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'details.*.subtotal' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'details.*.received_quantity' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'numeric', 'min:0'],
            'details.*.received_base_quantity' => [$this->isUpdate() ? 'nullable' : 'sometimes', 'numeric', 'min:0'],
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

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }
}
