<?php

namespace App\Http\Requests\CylinderType;

use Illuminate\Foundation\Http\FormRequest;

class StoreCylinderTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('cylinder-types.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand_id' => 'required|exists:cylinder_brands,id',
            'weight' => 'required|numeric|min:1',
            'is_active' => 'nullable|boolean',
        ];
    }
}
