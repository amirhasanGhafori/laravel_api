<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use App\Permissions\V1\Abilities;
use Tests\Feature\Api\ApiTestCase;

class LoginTest extends ApiTestCase
{
    public function test_it_returns_a_token_when_credentials_are_valid(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['token'],
                'message',
                'status',
            ])
            ->assertJsonPath('message', 'Authenticated')
            ->assertJsonPath('status', 200);

        $this->withToken($response->json('data.token'))
            ->getJson('/api/v1/tickets')
            ->assertOk();

        $this->assertEqualsCanonicalizing(
            Abilities::getAbilities($user),
            $user->tokens()->first()->abilities,
        );
    }

    public function test_it_returns_manager_abilities_on_the_token_for_managers(): void
    {
        $user = User::factory()->manager()->create([
            'email' => 'manager@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'manager@example.com',
            'password' => 'password',
        ])->assertOk();

        $this->assertEqualsCanonicalizing(
            Abilities::getAbilities($user),
            $user->tokens()->first()->abilities,
        );
    }

    public function test_it_returns_401_when_credentials_are_invalid(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid Credentials')
            ->assertJsonPath('status', 401);
    }

    public function test_it_returns_422_when_email_is_missing(): void
    {
        $this->postJson('/api/login', [
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_returns_422_when_password_is_missing(): void
    {
        $this->postJson('/api/login', [
            'email' => 'login@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_it_returns_422_when_password_is_shorter_than_eight_characters(): void
    {
        $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'short',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_it_returns_422_when_email_is_invalid(): void
    {
        $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
