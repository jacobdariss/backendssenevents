<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        $modules = ['partner', 'users', 'review', 'faqs'];
        $actions = ['view', 'add', 'edit', 'delete', 'restore', 'force_delete'];

        $created = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $name = $action . '_' . $module;
                $perm = Permission::firstOrCreate(['name' => $name], ['guard_name' => 'web', 'is_fixed' => true]);
                $created[] = $perm;
            }
        }

        $roles = Role::whereIn('name', ['admin', 'demo_admin'])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($created);
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        $modules = ['partner', 'users', 'review', 'faqs'];
        $actions = ['view', 'add', 'edit', 'delete', 'restore', 'force_delete'];

        $names = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $names[] = $action . '_' . $module;
            }
        }

        $permissions = Permission::whereIn('name', $names)->get();
        $roles = Role::whereIn('name', ['admin', 'demo_admin'])->get();
        foreach ($roles as $role) {
            $role->revokePermissionTo($permissions);
        }
        Permission::whereIn('name', $names)->delete();
    }
};
