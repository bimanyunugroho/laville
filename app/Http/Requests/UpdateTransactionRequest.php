<?php

namespace App\Http\Requests;

use App\Enums\StatusPayment;
use App\Enums\StatusTransaction;
use App\Enums\TypePayment;
use App\Enums\TypeSourceTransaction;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_number' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::unique(Transaction::class)->ignore(request()->route('transaction')->id)],
            'customer_id' => [$this->isUpdate() ? 'required' : 'nullable'],
            'transaction_date' => [$this->isUpdate() ? 'required' : 'sometimes', 'date'],
            'user_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:users,id'],
            'total' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'discount' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'subtotal' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'tax' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'total_amount' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'paid_amount' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'change_amount' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'status' => [$this->isUpdate() ? 'required' : 'sometimes', Rule::in(StatusTransaction::values())],
            'source_transaction' => [$this->isUpdate() ? 'required' : 'sometimes', Rule::in(TypeSourceTransaction::values())],
            'notes' => [$this->isUpdate() ? 'required' : 'nullable', 'string'],
            'is_active' => [$this->isUpdate() ? 'required' : 'sometimes', 'boolean'],
            'details' => [$this->isUpdate() ? 'required' : 'sometimes', 'array', 'min:1'],
            'details.*.product_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:products,id'],
            'details.*.unit_id' => [$this->isUpdate() ? 'required' : 'sometimes', 'exists:units,id'],
            'details.*.quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.base_quantity' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.price' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.discount' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'details.*.subtotal' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'payments' => [$this->isUpdate() ? 'required' : 'sometimes', 'array', 'min:1'],
            'payments.*.payment_date' => [$this->isUpdate() ? 'required' : 'sometimes', 'date'],
            'payments.*.payment_method' => [$this->isUpdate() ? 'required' : 'sometimes', Rule::in(TypePayment::values())],
            'payments.*.payment_reference' => [$this->isUpdate() ? 'required' : 'nullable', 'string'],
            'payments.*.amount' => [$this->isUpdate() ? 'required' : 'sometimes', 'numeric'],
            'payments.*.status' => [$this->isUpdate() ? 'required' : 'sometimes', Rule::in(StatusPayment::values())]
        ];
    }

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }

    public function attributes()
    {
        return [
            'details.*.product_id' => ['Detail Product'],
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
