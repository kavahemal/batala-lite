<?php

namespace App\Http\Requests\Purchase;

class UpdatePurchaseRequest extends StorePurchaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('purchase.edit', $this->route('purchase'));
    }
}
