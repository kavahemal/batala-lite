<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Truck;

class DriverTruckService
{
    public function resolve(array $data): array
    {
        $truck = Truck::withTrashed()->firstOrNew(['truck_no' => $data['truck_no']]);
        $truck->fill(['is_active' => true, 'deleted_at' => null])->save();

        $name = $data['driver_name'];
        $mobile = $data['driver_mobile'] ?? null;

        if ($mobile) {
            $driver = Driver::withTrashed()->firstOrNew(['mobile' => $mobile]);
            $driver->fill(['name' => $name, 'is_active' => true, 'deleted_at' => null])->save();
        } else {
            $driver = Driver::whereNull('mobile')->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
                ?? Driver::create(['name' => $name]);
            if (! $driver->is_active) {
                $driver->update(['is_active' => true]);
            }
        }

        unset($data['truck_no'], $data['driver_name'], $data['driver_mobile']);

        return $data + ['truck_id' => $truck->id, 'driver_id' => $driver->id];
    }

    public function suggestions(): array
    {
        return [
            'trucks' => Truck::active()->with('driver')->orderBy('truck_no')->get(),
            'drivers' => Driver::active()->orderBy('name')->orderBy('mobile')->get(),
        ];
    }
}
