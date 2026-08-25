<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Permissions\V1\Abilities;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  list<string>|null  $abilities
     */
    protected function actingAsApiUser(User $user, ?array $abilities = null): static
    {
        $token = $user->createToken(
            'test-token',
            $abilities ?? Abilities::getAbilities($user),
        )->plainTextToken;

        return $this->withToken($token);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function ticketPayload(int $authorId, array $overrides = []): array
    {
        return array_replace_recursive([
            'data' => [
                'attributes' => [
                    'title' => 'Test ticket',
                    'description' => 'A ticket description',
                    'status' => 'A',
                ],
                'relationships' => [
                    'author' => [
                        'data' => [
                            'id' => $authorId,
                        ],
                    ],
                ],
            ],
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function userPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'data' => [
                'attributes' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane@example.com',
                    'isManager' => false,
                    'password' => 'password12',
                ],
            ],
        ], $overrides);
    }

    /**
     * @return array<string, list<string>>
     */
    protected function ticketCollectionStructure(): array
    {
        return [
            'data' => [
                '*' => [
                    'type',
                    'id',
                    'attributes' => [
                        'title',
                        'status',
                        'created_at',
                    ],
                    'relationship',
                    'links',
                ],
            ],
            'links',
            'meta',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    protected function ticketResourceStructure(): array
    {
        return [
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'title',
                    'status',
                    'created_at',
                ],
                'relationship',
                'links',
            ],
        ];
    }
}
