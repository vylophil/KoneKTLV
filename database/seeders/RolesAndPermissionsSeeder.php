<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_unique(array_merge(...array_values(AccessControl::ROLE_PERMISSIONS))) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // DatabaseSeeder disables model events, so refresh before assigning permissions.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AccessControl::ROLE_PERMISSIONS as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissions);
        }

        User::query()->whereDoesntHave('roles')->eachById(function (User $user): void {
            $user->assignRole('User');
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
