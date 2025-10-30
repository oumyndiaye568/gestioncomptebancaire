<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Client;
use Laravel\Passport\Client as PassportClient;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un client OAuth pour les tests
        PassportClient::create([
            'name' => 'Test Client',
            'secret' => 'test-secret',
            'redirect' => 'http://localhost',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);
    }

    /** @test */
    public function admin_can_login_with_valid_credentials()
    {
        // Créer un admin de test
        $admin = Admin::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'nom' => 'Admin Test'
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'access_token',
                        'token_type',
                        'expires_in',
                        'refresh_token',
                        'admin' => [
                            'id',
                            'nom',
                            'email',
                            'role',
                            'scopes'
                        ]
                    ],
                    'message'
                ]);

        $this->assertEquals(true, $response->json('success'));
        $this->assertEquals('Connexion réussie', $response->json('message'));
    }

    /** @test */
    public function admin_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Identifiants invalides',
                    'error' => 'invalid_credentials'
                ]);
    }

    /** @test */
    public function admin_can_refresh_token()
    {
        $admin = Admin::factory()->create();

        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => 'dummy_token'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'access_token',
                        'token_type',
                        'expires_in'
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function admin_can_logout()
    {
        $admin = Admin::factory()->create();
        $token = $admin->createToken('Test Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Déconnexion réussie'
                ]);
    }

    /** @test */
    public function client_can_login_with_valid_credentials()
    {
        $client = Client::factory()->create([
            'email' => 'client@test.com',
            'password' => bcrypt('password123'),
            'nom_complet' => 'Client Test'
        ]);

        $response = $this->postJson('/api/client/login', [
            'email' => 'client@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'client',
                        'token'
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function client_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/client/login', [
            'email' => 'client@test.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Identifiants incorrects'
                ]);
    }

    /** @test */
    public function protected_admin_routes_require_authentication()
    {
        $response = $this->getJson('/api/v1/admin/test-auth');

        $response->assertStatus(401);
    }

    /** @test */
    public function protected_admin_routes_require_admin_role()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('Test Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/admin/test-auth');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_protected_routes_with_valid_token()
    {
        $admin = Admin::factory()->create();
        $token = $admin->createToken('Test Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/admin/test-auth');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Authentification réussie'
                ]);
    }
}