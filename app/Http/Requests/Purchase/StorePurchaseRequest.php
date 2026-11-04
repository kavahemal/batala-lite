<?php

namespace App\Http\Requests\Purchase;

use App\Http\Requests\Concerns\ValidatesStockItems;
use App\Http\Requests\Concerns\ValidatesTruckAndDriver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePurchaseRequest extends FormRequest
{
    use ValidatesStockItems, ValidatesTruckAndDriver;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('purchase.create');
    }

    protected function prepareForValidation()
    {
        $this->prepareTruckAndDriver();
        $this->prepareStockItems();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->truckAndDriverRules(mobileRequired: false) + [
            'purchase_date' => 'required|date',
            'remarks' => 'nullable|string',
        ] + $this->stockItemRules();
    }

    public function messages(): array
    {
        return $this->stockItemMessages();
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateStockItemBrands($validator);
            },
        ];
    }
}
