<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Truck extends Model
{
    /** @use HasFactory<\Database\Factories\TruckFactory> */
    use HasFactory, HasUserTracking, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class)->withTrashed();
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public static function cleanNo(?string $truckNo): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $truckNo));
    }

    public static function formatNo(?string $truckNo): string
    {
        return preg_replace('/^([A-Z]{2})([0-9]{2})([A-Z]{2})([0-9]{4})$/', '$1 $2 $3 $4', (string) $truckNo);
    }

    public function label(): string
    {
        return static::formatNo($this->truck_no);
    }
}
