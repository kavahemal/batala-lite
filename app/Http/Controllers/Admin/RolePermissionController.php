<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roleId = Auth::user()->roles->first()->id;
        $roles = Role::where('id', '>', $roleId)->get();

        return view('admin.role-permission.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $roleId = Auth::user()->roles->first()->id;
        if (! ($role->id > $roleId)) {
            abort(403, 'This action is unauthorized.');
        }
        // Locked (full version) modules have no permissions; drop them and any header left without rows
        $menus = collect(config('permissions'));
        $permissionsConfig = $menus
            ->reject(fn ($menu) => $menu['locked'] ?? false)
            ->filter(fn ($menu) => ! isset($menu['submenus'])
                || collect($menu['submenus'])->contains(fn ($key) => isset($menus[$key]['permissions'])))
            ->all();
        $assignedPermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.role-permission.edit', compact('role', 'permissionsConfig', 'assignedPermissions'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $roleId = Auth::user()->roles->first()->id;
        if (! ($role->id > $roleId)) {
            return response()->json(['message' => 'This action is unauthorized.', 'status' => false]);
        }
        $request->validate([
            'permission' => 'required|string|exists:permissions,name',
            'status' => 'required|boolean',
        ]);

        if ($request->status == 1) {
            if (! $role->hasPermissionTo($request->permission)) {
                $role->givePermissionTo($request->permission);
            }
        } else {
            if ($role->hasPermissionTo($request->permission)) {
                $role->revokePermissionTo($request->permission);
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        // $role->syncPermissions([$request->permission]);

        return response()->json(['message' => 'Permission updated successfully', 'status' => true]);
    }
}
