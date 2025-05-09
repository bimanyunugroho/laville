<?php

namespace App\Http\Requests;

use App\Enums\StatusClosedPeriod;
use App\Enums\StatusReceiptEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClosedPeriodRequest extends FormRequest
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
            'no_closed' => ['required', 'string', 'unique:closed_periods,no_closed'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
            'user_id' => ['required', 'exists:users,id'],
            'closed_date' => ['required', 'date'],
            'user_ack_id' => ['nullable', 'exists:users,id'],
            'ack_date' => ['nullable', 'date'],
            'user_reject_id' => ['nullable', 'exists:users,id'],
            'reject_date' => ['nullable', 'date'],
            'status_period' => ['required', 'string', Rule::in(StatusClosedPeriod::values())],
            'status_confirm' => ['required', 'string', Rule::in(StatusReceiptEnum::values())],
            'status' => ['boolean'],
        ];
    }
}
