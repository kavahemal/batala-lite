<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Fills created_by / updated_by / deleted_by with the logged-in user (nothing when no one is logged in, e.g. artisan / fake data).
 * Only for Eloquent models (@disregard: the editor can't see static::creating() etc. from a trait, PHP runs it fine).
 */
trait HasUserTracking
{
    protected static function bootHasUserTracking(): void
    {
        /** @disregard P1013 Eloquent model event, the trait is only used on models */
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        /** @disregard P1013 Eloquent model event, the trait is only used on models */
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        /** @disregard P1013 Eloquent model event, the trait is only used on models */
        static::deleting(function ($model) {
            if (
                Auth::check() &&
                in_array(SoftDeletes::class, class_uses_recursive($model))
            ) {
                $model->deleted_by = Auth::id();
                // Save without triggering events again
                $model->saveQuietly();
            }
        });
    }

    /** Relationships (available in ALL models using this trait) */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
