<?php

namespace Tests\Feature\Auth;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email'    => 'test@senevents.africa',
            'password' => Hash::make('password123'),
        ], $overrides));
    }

    /** @test */
    public function user_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/login', [
            'email'     => 'test@senevents.africa',
            'password'  => 'password123',
            'device_id' => 'device-test-001',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'data' => ['token']]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/login', [
            'email'    => 'test@senevents.africa',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_access_protected_routes(): void
    {
        $user  = $this->createUser();
        $token = $user->createToken('test-device')->plainTextToken;

        Device::create([
            'user_id'   => $user->id,
            'device_id' => 'test-device',
            'platform'  => 'Android',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                         ->getJson('/api/v3/continuewatch-list');

        $response->assertStatus(200);
    }

    /** @test */
    public function logout_all_revokes_token_and_device(): void
    {
        $user  = $this->createUser();
        $token = $user->createToken('device-logout-test')->plainTextToken;

        Device::create([
            'user_id'   => $user->id,
            'device_id' => 'device-logout-test',
            'platform'  => 'iOS',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                         ->postJson('/api/logout-all');

        $response->assertStatus(200);

        // Le device doit être supprimé
        $this->assertDatabaseMissing('devices', [
            'user_id'   => $user->id,
            'device_id' => 'device-logout-test',
        ]);
    }

    /** @test */
    public function request_without_token_is_rejected_on_protected_routes(): void
    {
        $response = $this->getJson('/api/v3/continuewatch-list');
        $response->assertStatus(401);
    }
}
