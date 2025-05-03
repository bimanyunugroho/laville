<?php

namespace App\Http\Requests;

use App\Enums\StatusPOEnum;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StorePurchaseOrderRequest extends FormRequest
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
            'po_number' => ['required', 'string', 'unique:purchase_orders,po_number'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'user_id' => ['required', 'exists:users,id'],
            'user_ack_id' => ['nullable', 'exists:users,id'],
            'po_date' => ['required', 'date_format:Y-m-d H:i:s'],
            'expected_date' => ['required', 'date_format:Y-m-d H:i:s'],
            'ack_date' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'user_reject_id' => ['nullable', 'exists:users,id'],
            'reject_date' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'tax' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'total_net' => ['required', 'numeric'],
            'status' => ['required', Rule::in(StatusPOEnum::values())],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'details' => ['required', 'array', 'min:1'],
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
        ];
    }
}
