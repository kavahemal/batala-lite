<?php

namespace App\Http\Requests\Booking;

use App\Enums\BookingStatus;
use App\Http\Requests\Concerns\ValidatesStockItems;
use App\Services\StockService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    use ValidatesStockItems;

    /**
     * Cylinder types booked beyond available stock: cylinder_type_id => short by
     *
     * @var array<int, int>
     */
    protected array $shortages = [];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('booking.create');
    }

    protected function prepareForValidation()
    {
        $this->prepareStockItems();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentCustomerId = $this->route('booking')?->customer_id;

        return [
            // delivered is set by "Mark delivered" only
            'status' => ['required', Rule::in([BookingStatus::PENDING->value, BookingStatus::CANCELLED->value])],
            'customer_id' => [
                'required',
                // Active customers only; on edit the booking's current customer stays valid
                Rule::exists('customers', 'id')->whereNull('deleted_at')
                    ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $currentCustomerId)),
            ],
            'booking_date' => 'required|date',
            'scheduled_date' => 'nullable|date|after_or_equal:booking_date',
            'remarks' => 'nullable|string',
        ] + $this->stockItemRules();
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'The customer field is required.',
            'customer_id.exists' => 'Select an active customer.',
            'scheduled_date.after_or_equal' => 'The scheduled date cannot be before the booking date.',
        ] + $this->stockItemMessages();
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateStockItemBrands($validator);
                $this->detectShortages();

                if ($this->isShortStock() && blank($this->input('remarks'))) {
                    $validator->errors()->add('remarks', 'Some cylinders are short of stock - add a note in remarks (e.g. customer is OK to wait).');
                }
            },
        ];
    }

    /**
     * Only pending bookings reserve stock, so only they can be short
     */
    protected function detectShortages(): void
    {
        if ($this->input('status') !== BookingStatus::PENDING->value) {
            return;
        }

        $available = app(StockService::class)->available($this->route('booking')?->id);

        foreach ($this->quantitiesByType() as $typeId => $quantity) {
            $short = $quantity - ($available[$typeId] ?? 0);
            if ($short > 0) {
                $this->shortages[$typeId] = $short;
            }
        }
    }

    public function isShortStock(): bool
    {
        return count($this->shortages) > 0;
    }
}
