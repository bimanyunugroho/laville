<?php

namespace App\Http\Requests;

use App\Enums\StatusClosedPeriod;
use App\Enums\StatusReceiptEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClosedPeriodFormRequest extends FormRequest
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
            'status_period' => ['nullable', Rule::in(StatusClosedPeriod::values())]
        ];
    }
}
