<?php

namespace App\Http\Requests;

use App\Enums\StatusPayment;
use App\Enums\StatusTransaction;
use App\Enums\TypePayment;
use App\Enums\TypeSourceTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
            'invoice_number' => ['required', 'string', 'unique:transactions,invoice_number'],
            'customer_id' => ['nullable'],
            'transaction_date' => ['required', 'date'],
            'user_id' => ['required', 'exists:users,id'],
            'total' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'subtotal' => ['required', 'numeric'],
            'tax' => ['required', 'numeric'],
            'total_amount' => ['required', 'numeric'],
            'paid_amount' => ['required', 'numeric'],
            'change_amount' => ['required', 'numeric'],
            'status' => ['required', Rule::in(StatusTransaction::values())],
            'source_transaction' => ['required', Rule::in(TypeSourceTransaction::values())],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.unit_id' => ['required', 'exists:units,id'],
            'details.*.quantity' => ['required', 'numeric'],
            'details.*.base_quantity' => ['required', 'numeric'],
            'details.*.price' => ['required', 'numeric'],
            'details.*.discount' => ['required', 'numeric'],
            'details.*.subtotal' => ['required', 'numeric'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_date' => ['required', 'date'],
            'payments.*.payment_method' => ['required', Rule::in(TypePayment::values())],
            'payments.*.payment_reference' => ['nullable', 'string'],
            'payments.*.amount' => ['required', 'numeric'],
            'payments.*.status' => ['required', Rule::in(StatusPayment::values())]
        ];
    }

    public function attributes()
    {
        return [
            'details.*.product_id' => ['Detail Product'],
            'details.*.unit_id' => ['Detail Unit'],
            'details.*.quantity' => ['Detail Quantity'],
            'details.*.price' => ['Detail Price'],
            'details.*.discount' => ['Detail Discount'],
            'details.*.subtotal' => ['Details Subtotal'],
            'payments.*.payment_date' => ['Payment Date'],
            'payments.*.payment_method' => ['Payment Method'],
            'payments.*.payment_reference' => ['Payment Reference'],
            'payments.*.amount' => ['Payment Amount'],
            'payments.*.status' => ['Payment Status']
        ];
    }
}
