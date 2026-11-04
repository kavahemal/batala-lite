<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait Filterable
{
    /**
     * Scope a query to only include active records (where is_active is true).
     */
    public function scopeActive(Builder $query): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'is_active')) {
            $query->where('is_active', true);
        }

        return $query;
    }
}
