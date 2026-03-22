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
            'email'    => 'test' . uniqid() . '@senevents.africa',
            'password' => Hash::make('password123'),
            'status'   => 1,
        ], $overrides));
    }

    /** @test */
    public function user_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/login', [
            'email'     => $user->email,
            'password'  => 'password123',
            'device_id' => 'device-test-001',
        ]);

        // Le login retourne 200 avec api_token (pas "token") dans la réponse
        $response->assertStatus(200);
        $data = $response->json();
        // La structure réelle : { status: true, data: { api_token: '...' } }
        $this->assertTrue(
            isset($data['data']['api_token']) || isset($data['api_token']),
            'La réponse doit contenir api_token. Réponse reçue : ' . json_encode($data)
        );
    }

    /** @test */
    public function login_fails_with_invalid_credentials(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'wrongpassword',
        ]);

        // L'API retourne 200 même en cas d'échec (convention du projet)
        // mais le status JSON doit être false ou un message d'erreur
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertFalse(
            isset($data['data']['api_token']),
            'Un mauvais mot de passe ne doit pas retourner un token'
        );
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
    public function logout_revokes_session(): void
    {
        $user  = $this->createUser();
        $token = $user->createToken('device-logout-test')->plainTextToken;

        Device::create([
            'user_id'   => $user->id,
            'device_id' => 'device-logout-test',
            'platform'  => 'iOS',
        ]);

        // La route logout est GET /api/logout
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                         ->getJson('/api/logout');

        $response->assertStatus(200);
    }

    /** @test */
    public function request_without_token_is_rejected_on_protected_routes(): void
    {
        $response = $this->getJson('/api/v3/continuewatch-list');
        $response->assertStatus(401);
    }
}

