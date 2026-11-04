<?php

namespace App\Http\Requests\Customer;

class UpdateCustomerRequest extends StoreCustomerRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('customers.edit');
    }
}
