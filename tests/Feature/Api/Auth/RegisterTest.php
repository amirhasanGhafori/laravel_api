<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Tests\Feature\Api\ApiTestCase;

class RegisterTest extends ApiTestCase
{
    public function test_it_returns_the_username_and_does_not_create_a_user(): void
    {
        $response = $this->postJson('/api/register', [
            'username' => 'amirhasan',
            'email' => 'amirhasan@example.com',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'amirhasan')
            ->assertJsonPath('data', [])
            ->assertJsonPath('status', 200);

        $this->assertSame(0, User::query()->count());
    }

    public function test_it_returns_422_when_username_is_missing(): void
    {
        $this->postJson('/api/register', [
            'email' => 'amirhasan@example.com',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username']);
    }

    public function test_it_returns_422_when_email_is_missing(): void
    {
        $this->postJson('/api/register', [
            'username' => 'amirhasan',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_returns_422_when_password_is_missing(): void
    {
        $this->postJson('/api/register', [
            'username' => 'amirhasan',
            'email' => 'amirhasan@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
