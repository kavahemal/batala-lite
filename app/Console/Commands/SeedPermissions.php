<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SeedPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-permissions {--detach}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed all menu permissions into the database and optionally detach them from roles.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $menus = config('permissions');
        $permissions = [];

        // Check if the --detach flag is passed
        $detach = $this->option('detach');

        if ($detach) {
            $this->info('Detaching all permissions from all roles...');

            // Detach all permissions from all roles
            $roles = Role::all();
            foreach ($roles as $role) {
                $role->syncPermissions([]); // Remove all permissions from the role
            }

            $this->info('All permissions have been detached from all roles successfully!');
        }

        foreach ($menus as $menu) {
            if (isset($menu['permissions'])) {
                $pre_assigned_permissions = [];
                foreach ($menu['permissions'] as $permission) {
                    if (! $permission) {
                        continue;
                    }
                    $temp = Permission::firstOrCreate(['name' => $permission]);
                    $permissions[] = $temp->name;
                    $pre_assigned_permissions[] = $permission;
                }

                if (isset($menu['pre_assigned_roles']) && count($menu['pre_assigned_roles'])) {
                    foreach ($menu['pre_assigned_roles'] as $roleName) {
                        $role = Role::findByName($roleName);
                        $role->givePermissionTo($pre_assigned_permissions);
                        // $role->syncPermissions([$pre_assigned_permissions]);
                    }
                }

                // Roles that only get the "view" permission of this menu
                foreach ($menu['pre_assigned_view_roles'] ?? [] as $roleName) {
                    if (! empty($menu['permissions']['view'])) {
                        Role::findByName($roleName)->givePermissionTo($menu['permissions']['view']);
                    }
                }
            }
        }
        Permission::whereNotIn('name', $permissions)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('All permissions have been seeded successfully!');
    }
}
