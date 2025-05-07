<?php

namespace App\Http\Requests;

use App\Enums\StatusStockOpnameEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockOpnameRequest extends FormRequest
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
            'so_number' => ['required', 'string', 'unique:stock_opnames,so_number'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
            'user_id' => ['required', 'exists:users,id'],
            'so_date' => ['required', 'date_format:Y-m-d H:i:s'],
            'user_validator_id' => ['nullable', 'exists:users,id'],
            'validator_date' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'user_ack_id' => ['nullable', 'exists:users,id'],
            'ack_date' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'user_reject_id' => ['nullable', 'exists:users,id'],
            'reject_date' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'subtotal' => ['required', 'numeric'],
            'tax' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'total_net' => ['required', 'numeric'],
            'status' => ['required', Rule::in(StatusStockOpnameEnum::values())],
            'notes' => ['nullable', 'string'],
            'is_locked' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.unit_id' => ['required', 'exists:units,id'],
            'details.*.system_stock' => ['required', 'numeric'],
            'details.*.system_stock_base' => ['required', 'numeric'],
            'details.*.physical_stock' => ['required', 'numeric'],
            'details.*.physical_stock_base' => ['required', 'numeric'],
            'details.*.difference_stock' => ['required', 'numeric'],
            'details.*.difference_stock_base' => ['required', 'numeric'],
            'details.*.price' => ['required', 'numeric'],
            'details.*.subtotal' => ['required', 'numeric'],
            'details.*.status' => ['required', 'string'],
            'details.*.notes' => ['required', 'string'],
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
}
