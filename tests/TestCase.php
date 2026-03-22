<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        // Vider le cache Spatie à chaque test pour que assignRole() soit immédiatement visible
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function signInAsAdmin($user = null)
    {
        $user = $user ?: User::factory()->create();

        $admin = Role::create(['name' => 'admin', 'title' => 'Admin']);

        $user->assignRole('admin');

        $this->actingAs($user);

        return $user;
    }
}
