<?php

namespace App\Traits;

use App\Models\Driver;
use App\Models\Truck;

trait HasTruckAndDriver
{
    protected static function bootHasTruckAndDriver(): void
    {
        /** @disregard P1013 Eloquent model event, the trait is only used on models */
        static::saved(function ($model) {
            if ($model->wasRecentlyCreated || $model->wasChanged(['truck_id', 'driver_id'])) {
                $model->truck?->update(['driver_id' => $model->driver_id]);
            }
        });
    }

    public function truck()
    {
        return $this->belongsTo(Truck::class)->withTrashed();
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class)->withTrashed();
    }
}
