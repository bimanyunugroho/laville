<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
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
            'code'  => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::unique(Unit::class)->ignore(request()->route('unit')->id)],
            'name'  => [$this->isUpdate() ? 'required' : 'sometimes', 'string', Rule::unique(Unit::class)->ignore(request()->route('unit')->id)],
            'is_active' => [$this->isUpdate() ? 'required' : 'sometimes', 'boolean']
        ];
    }

    private function isUpdate()
    {
        return request()->isMethod('PUT') || request()->isMethod('PATCH');
    }
}
