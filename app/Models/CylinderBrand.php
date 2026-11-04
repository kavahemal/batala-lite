<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CylinderBrand extends Model
{
    use Filterable, HasUserTracking, SoftDeletes;

    protected $guarded = [];

    public function cylinderTypes()
    {
        return $this->hasMany(CylinderType::class, 'brand_id');
    }
}
