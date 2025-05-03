<?php

namespace App\Http\Requests;

use App\Enums\StatusReceiptEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApprovalGoodReceiptRequest extends FormRequest
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
            'user_ack_id' => [
                Rule::requiredIf(request()->filled('ack_date')),
                'nullable',
                'exists:users,id'
            ],
            'ack_date' => [
                Rule::requiredIf(request()->filled('user_ack_id')),
                'nullable',
                'date'
            ],
            'user_reject_id' => [
                Rule::requiredIf(request()->filled('reject_date')),
                'nullable',
                'exists:users,id'
            ],
            'reject_date' => [
                Rule::requiredIf(request()->filled('user_reject_id')),
                'nullable',
                'date'
            ],
            'status_receipt' => ['nullable', Rule::in(StatusReceiptEnum::values())]
        ];
    }
}
