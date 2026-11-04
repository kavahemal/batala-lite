<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CylinderType extends Model
{
    use Filterable, HasUserTracking, SoftDeletes;

    protected $guarded = [];

    public function cylinderBrand()
    {
        return $this->belongsTo(CylinderBrand::class, 'brand_id');
    }
}
