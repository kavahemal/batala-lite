<?php

namespace App\Http\Requests\Concerns;

use App\Models\Truck;
use Illuminate\Support\Str;

trait ValidatesTruckAndDriver
{
    protected function prepareTruckAndDriver(): void
    {
        $this->merge([
            'truck_no' => Truck::cleanNo($this->input('truck_no')),
            'driver_name' => Str::squish((string) $this->input('driver_name')),
            'driver_mobile' => trim((string) $this->input('driver_mobile')) ?: null,
        ]);
    }

    protected function truckAndDriverRules(bool $mobileRequired, array $truckRules = []): array
    {
        return [
            'truck_no' => array_merge(['required', 'string', 'max:10', 'regex:/^[A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{4}$/'], $truckRules),
            'driver_name' => 'required|string|max:100',
            'driver_mobile' => ($mobileRequired ? 'required' : 'nullable').'|digits:10',
        ];
    }
}
