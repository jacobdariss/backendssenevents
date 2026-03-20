<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Rôle super_admin (fixe)
        Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['title' => 'Super Admin', 'is_fixed' => true]
        );

        // Rôle partner (fixe — géré par le système)
        Role::firstOrCreate(
            ['name' => 'partner', 'guard_name' => 'web'],
            ['title' => 'Partner', 'is_fixed' => true]
        );

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        Role::where('name', 'partner')->where('is_fixed', true)->delete();
        Role::where('name', 'super_admin')->where('is_fixed', true)->delete();
    }
};
