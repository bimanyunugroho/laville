<?php

namespace App\Http\Requests;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
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
            'code' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::unique(Supplier::class)->ignore(request()->route('supplier')->id)],
            'name' => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::unique(Supplier::class)->ignore(request()->route('supplier')->id)],
            'phone' => [$this->isUpdate() ? 'required' : 'sometimes', 'string'],
            'email' => [$this->isUpdate() ? 'required' : 'sometimes', 'string'],
            'address' => [$this->isUpdate() ? 'required' : 'sometimes', 'string'],
            'is_active' => [$this->isUpdate() ? 'required' : 'sometimes', 'boolean']
        ];
    }

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }
}
