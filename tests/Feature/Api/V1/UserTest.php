<?php

namespace Tests\Feature\Api\V1;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Api\ApiTestCase;

class UserTest extends ApiTestCase
{
    public function test_index_returns_only_users_who_have_tickets(): void
    {
        $author = User::factory()->create();
        User::factory()->create();
        Ticket::factory()->recycle($author)->create();

        $viewer = User::factory()->create();

        $this->actingAsApiUser($viewer)
            ->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => [
                            'name',
                            'email',
                            'isManager',
                            'emailVerifiedAt',
                            'createdAt',
                            'updatedAt',
                        ],
                        'links',
                    ],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $author->id);
    }

    public function test_index_returns_empty_collection_when_no_tickets_exist(): void
    {
        $viewer = User::factory()->create();
        User::factory()->create();

        $this->actingAsApiUser($viewer)
            ->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_manager_can_store_a_user(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAsApiUser($manager)
            ->postJson('/api/v1/users', $this->userPayload())
            ->assertStatus(201)
            ->assertJsonPath('data.attributes.name', 'Jane Doe')
            ->assertJsonPath('data.attributes.email', 'jane@example.com')
            ->assertJsonPath('data.attributes.isManager', false);

        $created = User::query()->where('email', 'jane@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue(Hash::check('password12', $created->password));
    }

    public function test_non_manager_cannot_store_a_user(): void
    {
        $user = User::factory()->create();

        $this->actingAsApiUser($user)
            ->postJson('/api/v1/users', $this->userPayload())
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to store that resource.');
    }

    public function test_store_returns_422_when_required_fields_are_missing(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAsApiUser($manager)
            ->postJson('/api/v1/users', ['data' => ['attributes' => []]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'data.attributes.name',
                'data.attributes.email',
                'data.attributes.isManager',
                'data.attributes.password',
            ]);
    }

    public function test_store_returns_422_when_email_is_invalid(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAsApiUser($manager)
            ->postJson('/api/v1/users', $this->userPayload([
                'data' => ['attributes' => ['email' => 'not-an-email']],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    public function test_show_returns_a_user_resource(): void
    {
        $viewer = User::factory()->create();
        $user = User::factory()->create();

        $this->actingAsApiUser($viewer)
            ->getJson("/api/v1/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.attributes.email', $user->email)
            ->assertJsonPath('data.attributes.isManager', false);
    }

    public function test_show_returns_200_when_the_user_does_not_exist(): void
    {
        $viewer = User::factory()->create();

        $this->actingAsApiUser($viewer)
            ->getJson('/api/v1/users/999999')
            ->assertOk()
            ->assertJsonPath('message', 'User Not Found')
            ->assertJsonPath('data.error', 'the provided user id does not exists.')
            ->assertJsonPath('status', 200);
    }

    public function test_manager_can_update_a_user(): void
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create(['name' => 'Old Name']);

        $this->actingAsApiUser($manager)
            ->patchJson("/api/v1/users/{$user->id}", [
                'data' => ['attributes' => ['name' => 'New Name']],
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'New Name');

        $this->assertSame('New Name', $user->refresh()->name);
    }

    public function test_non_manager_cannot_update_a_user(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAsApiUser($user)
            ->patchJson("/api/v1/users/{$target->id}", [
                'data' => ['attributes' => ['name' => 'Nope']],
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to update that resource.');
    }

    public function test_update_returns_422_when_email_is_invalid(): void
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create();

        $this->actingAsApiUser($manager)
            ->patchJson("/api/v1/users/{$user->id}", [
                'data' => ['attributes' => ['email' => 'not-an-email']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    public function test_update_returns_404_for_missing_users(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAsApiUser($manager)
            ->patchJson('/api/v1/users/999999', [
                'data' => ['attributes' => ['name' => 'Nope']],
            ])
            ->assertNotFound();
    }

    public function test_manager_can_replace_a_user(): void
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create();

        $this->actingAsApiUser($manager)
            ->putJson("/api/v1/users/{$user->id}", $this->userPayload([
                'data' => [
                    'attributes' => [
                        'name' => 'Replaced User',
                        'email' => 'replaced@example.com',
                        'isManager' => true,
                    ],
                ],
            ]))
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Replaced User')
            ->assertJsonPath('data.attributes.email', 'replaced@example.com')
            ->assertJsonPath('data.attributes.isManager', true);
    }

    public function test_non_manager_cannot_replace_a_user(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAsApiUser($user)
            ->putJson("/api/v1/users/{$target->id}", $this->userPayload())
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to Replace that resource.');
    }

    public function test_replace_returns_422_when_required_fields_are_missing(): void
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create();

        $this->actingAsApiUser($manager)
            ->putJson("/api/v1/users/{$user->id}", ['data' => ['attributes' => []]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'data.attributes.name',
                'data.attributes.email',
                'data.attributes.isManager',
                'data.attributes.password',
            ]);
    }

    public function test_replace_returns_404_for_missing_users_with_ticket_not_found_message(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAsApiUser($manager)
            ->putJson('/api/v1/users/999999', $this->userPayload())
            ->assertNotFound()
            ->assertJsonPath('message', 'Ticket Not Found');
    }

    public function test_manager_can_delete_a_user(): void
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create();

        $this->actingAsApiUser($manager)
            ->deleteJson("/api/v1/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('message', 'User Successfully Delete');

        $this->assertModelMissing($user);
    }

    public function test_non_manager_cannot_delete_a_user(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAsApiUser($user)
            ->deleteJson("/api/v1/users/{$target->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to Delete that resource.');

        $this->assertModelExists($target);
    }

    public function test_destroy_returns_404_for_missing_users_with_ticket_not_found_message(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAsApiUser($manager)
            ->deleteJson('/api/v1/users/999999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Ticket not found.');
    }
}
