<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Tests\Feature\Api\ApiTestCase;

class CurrentUserTest extends ApiTestCase
{
    public function test_it_returns_the_authenticated_user_from_api_user(): void
    {
        $user = User::factory()->create();

        $this->actingAsApiUser($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email)
            ->assertJsonMissingPath('password');
    }

    public function test_it_returns_the_authenticated_user_from_v1_user(): void
    {
        $user = User::factory()->create();

        $this->actingAsApiUser($user)
            ->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email);
    }

    public function test_it_returns_401_for_api_user_when_unauthenticated(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_it_returns_401_for_v1_user_when_unauthenticated(): void
    {
        $this->getJson('/api/v1/user')->assertUnauthorized();
    }
}
