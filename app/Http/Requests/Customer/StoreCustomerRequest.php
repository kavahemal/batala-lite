<?php

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('customers.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:'.Customer::NAME_REGEX],
            // On edit, the customer's own mobile doesn't count as used
            'mobile_number' => ['required', 'digits:10', Customer::uniqueMobileRule($this->route('customer')?->id)],
            'address' => 'nullable',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return ['name.regex' => Customer::NAME_MESSAGE];
    }

    /**
     * An unticked switch isn't posted: save it as inactive
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->has('is_active'),
        ]);
    }
}
