<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_and_logout(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
        ];

        // Register
        $register = $this->postJson('/api/users', $payload);
        $register->assertStatus(201)
            ->assertJsonStructure(['user' => ['id', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);

        // Login
        $login = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $login->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'email'], 'token']);

        $token = $login->json('token');

        // Logout
        // check profile (authenticated)
        $profile = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/profile');

        $profile->assertStatus(200)
            ->assertJsonPath('user.email', 'testuser@example.com');

        // revoke current token only
        $logout = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $logout->assertStatus(200)
            ->assertJson(['message' => 'Logged out']);

        // create another token and test logout-all
        $user = \App\Models\User::where('email', 'testuser@example.com')->first();
        $tokenA = $user->createToken('a')->plainTextToken;
        $tokenB = $user->createToken('b')->plainTextToken;

        // revoke all tokens
        $rev = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->postJson('/api/logout-all');

        $rev->assertStatus(200)
            ->assertJson(['message' => 'All tokens revoked']);

        // tokens table should be empty after revocation
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
