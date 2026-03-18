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

        // Ensure the super-admin role exists
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin'],
            ['title' => 'Super Admin', 'is_fixed' => true]
        );

        // Give ALL permissions to super-admin
        $superAdmin->syncPermissions(Permission::all());

        // Also ensure admin and demo_admin have all permissions (including any newly added ones)
        $roles = Role::whereIn('name', ['admin', 'demo_admin'])->get();
        foreach ($roles as $role) {
            $role->syncPermissions(Permission::all());
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

        // Revoke partner permissions from super-admin only (safe rollback)
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $partnerPermissions = Permission::where('name', 'like', '%_partner')->get();
            $superAdmin->revokePermissionTo($partnerPermissions);
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }
};
