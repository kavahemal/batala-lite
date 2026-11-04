<?php

namespace App\Policies;

use App\Models\CylinderBrand;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CylinderBrandPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('cylinder-brands.index');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CylinderBrand $cylinderBrand): bool
    {
        return $user->hasPermissionTo('cylinder-brands.index');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('cylinder-brands.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CylinderBrand $cylinderBrand): bool
    {
        return $user->hasPermissionTo('cylinder-brands.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CylinderBrand $cylinderBrand): bool
    {
        return $user->hasPermissionTo('cylinder-brands.destroy');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CylinderBrand $cylinderBrand): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CylinderBrand $cylinderBrand): bool
    {
        return false;
    }
}
