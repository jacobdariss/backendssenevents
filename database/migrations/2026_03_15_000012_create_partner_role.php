<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        // Create partner role
        $partnerRole = Role::firstOrCreate(
            ['name' => 'partner'],
            ['title' => 'Partner', 'is_fixed' => true, 'guard_name' => 'web']
        );

        // Permissions a partner should have: view/add/edit their own content
        $partnerPermissions = [
            'view_movie', 'add_movie', 'edit_movie',
            'view_tvshow', 'add_tvshow', 'edit_tvshow',
            'view_video', 'add_video', 'edit_video',
            'view_livetv', 'add_livetv', 'edit_livetv',
            'view_partner',
        ];

        foreach ($partnerPermissions as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            if (!$partnerRole->hasPermissionTo($permName)) {
                $partnerRole->givePermissionTo($permission);
            }
        }
    }

    public function down(): void
    {
        $role = Role::where('name', 'partner')->first();
        if ($role) {
            $role->delete();
        }
    }
};
