<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use App\Permissions\V1\Abilities;
use Tests\Feature\Api\ApiTestCase;

class LogoutTest extends ApiTestCase
{
    public function test_it_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token', Abilities::getAbilities($user))->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'logout successfyly')
            ->assertJsonPath('status', 200);

        $this->assertSame(0, $user->tokens()->count());

        $this->withToken($token)
            ->getJson('/api/v1/tickets')
            ->assertUnauthorized();
    }

    public function test_it_returns_401_when_unauthenticated(): void
    {
        $this->postJson('/api/logout')->assertUnauthorized();
    }
}
