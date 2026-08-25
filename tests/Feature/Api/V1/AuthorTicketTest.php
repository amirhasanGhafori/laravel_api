<?php

namespace Tests\Feature\Api\V1;

use App\Models\Ticket;
use App\Models\User;
use Tests\Feature\Api\ApiTestCase;

class AuthorTicketTest extends ApiTestCase
{
    public function test_index_returns_only_tickets_for_the_given_author(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        Ticket::factory()->recycle($author)->count(2)->create();
        Ticket::factory()->recycle($other)->create();

        $this->actingAsApiUser($author)
            ->getJson("/api/v1/users/{$author->id}/tickets")
            ->assertOk()
            ->assertJsonStructure($this->ticketCollectionStructure())
            ->assertJsonCount(2, 'data');
    }

    public function test_index_returns_empty_collection_when_author_has_no_tickets(): void
    {
        $author = User::factory()->create();

        $this->actingAsApiUser($author)
            ->getJson("/api/v1/users/{$author->id}/tickets")
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_manager_can_store_a_nested_ticket(): void
    {
        $manager = User::factory()->manager()->create();
        $author = User::factory()->create();

        $this->actingAsApiUser($manager)
            ->postJson("/api/v1/users/{$author->id}/tickets", $this->ticketPayload($author->id))
            ->assertStatus(201)
            ->assertJsonPath('data.attributes.title', 'Test ticket')
            ->assertJsonPath('data.relationship.author.data.id', (string) $author->id);

        $this->assertSame(1, Ticket::query()->where('user_id', $author->id)->count());
    }

    public function test_nested_store_merges_author_id_from_the_route(): void
    {
        $manager = User::factory()->manager()->create();
        $author = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAsApiUser($manager)
            ->postJson("/api/v1/users/{$author->id}/tickets", $this->ticketPayload($other->id))
            ->assertStatus(201)
            ->assertJsonPath('data.relationship.author.data.id', (string) $author->id);
    }

    public function test_non_manager_cannot_store_a_nested_ticket(): void
    {
        $user = User::factory()->create();

        $this->actingAsApiUser($user)
            ->postJson("/api/v1/users/{$user->id}/tickets", [
                'data' => [
                    'attributes' => [
                        'title' => 'Test ticket',
                        'description' => 'A ticket description',
                        'status' => 'A',
                    ],
                ],
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to store that resource.');
    }

    public function test_nested_store_returns_422_when_required_fields_are_missing(): void
    {
        $manager = User::factory()->manager()->create();
        $author = User::factory()->create();

        $this->actingAsApiUser($manager)
            ->postJson("/api/v1/users/{$author->id}/tickets", ['data' => ['attributes' => []]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'data.attributes.title',
                'data.attributes.description',
                'data.attributes.status',
            ]);
    }

    public function test_owner_can_update_a_nested_ticket(): void
    {
        $owner = User::factory()->create();
        $ticket = Ticket::factory()->recycle($owner)->create();

        $this->actingAsApiUser($owner)
            ->patchJson("/api/v1/users/{$owner->id}/tickets/{$ticket->id}", [
                'data' => ['attributes' => ['title' => 'Nested update']],
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.title', 'Nested update')
            ->assertJsonPath('data.attributes.description', $ticket->description);
    }

    public function test_nested_update_returns_404_when_ticket_belongs_to_another_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = Ticket::factory()->recycle($other)->create();

        $this->actingAsApiUser($owner)
            ->patchJson("/api/v1/users/{$owner->id}/tickets/{$ticket->id}", [
                'data' => ['attributes' => ['title' => 'Nope']],
            ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Ticket Not Found');
    }

    public function test_nested_update_returns_403_when_user_cannot_update_the_ticket(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();
        $ticket = Ticket::factory()->recycle($author)->create();

        $this->actingAsApiUser($user)
            ->patchJson("/api/v1/users/{$author->id}/tickets/{$ticket->id}", [
                'data' => ['attributes' => ['title' => 'Nope']],
            ])
            ->assertForbidden();
    }

    public function test_nested_update_returns_422_when_status_is_invalid(): void
    {
        $owner = User::factory()->create();
        $ticket = Ticket::factory()->recycle($owner)->create();

        $this->actingAsApiUser($owner)
            ->patchJson("/api/v1/users/{$owner->id}/tickets/{$ticket->id}", [
                'data' => ['attributes' => ['status' => 'bad']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data.attributes.status']);
    }

    public function test_nested_replace_updates_the_ticket(): void
    {
        $owner = User::factory()->create();
        $ticket = Ticket::factory()->recycle($owner)->create();

        $this->actingAsApiUser($owner)
            ->putJson(
                "/api/v1/users/{$owner->id}/tickets/{$ticket->id}",
                $this->ticketPayload($owner->id, [
                    'data' => ['attributes' => ['title' => 'Nested replace', 'status' => 'H']],
                ]),
            )
            ->assertOk()
            ->assertJsonPath('data.attributes.title', 'Nested replace')
            ->assertJsonPath('data.attributes.status', 'H');
    }

    public function test_nested_replace_returns_404_when_ticket_belongs_to_another_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = Ticket::factory()->recycle($other)->create();

        $this->actingAsApiUser($owner)
            ->putJson("/api/v1/users/{$owner->id}/tickets/{$ticket->id}", $this->ticketPayload($owner->id))
            ->assertNotFound()
            ->assertJsonPath('message', 'Ticket Not Found');
    }

    public function test_nested_replace_returns_403_when_token_lacks_replace_ability(): void
    {
        $owner = User::factory()->create();
        $ticket = Ticket::factory()->recycle($owner)->create();

        $this->actingAsApiUser($owner, [])
            ->putJson("/api/v1/users/{$owner->id}/tickets/{$ticket->id}", $this->ticketPayload($owner->id))
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to replace that resource.');
    }

    public function test_nested_replace_returns_422_when_required_fields_are_missing(): void
    {
        $owner = User::factory()->create();
        $ticket = Ticket::factory()->recycle($owner)->create();

        $this->actingAsApiUser($owner)
            ->putJson("/api/v1/users/{$owner->id}/tickets/{$ticket->id}", ['data' => ['attributes' => []]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'data.attributes.title',
                'data.attributes.description',
                'data.attributes.status',
                'data.relationships.author.data.id',
            ]);
    }

    public function test_nested_destroy_deletes_the_ticket_but_returns_403(): void
    {
        $owner = User::factory()->create();
        $ticket = Ticket::factory()->recycle($owner)->create();

        $this->actingAsApiUser($owner)
            ->deleteJson("/api/v1/users/{$owner->id}/tickets/{$ticket->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Ticket Not Deleted.You Not Permission')
            ->assertJsonPath('status', 403);

        $this->assertModelMissing($ticket);
    }

    public function test_nested_destroy_returns_404_when_ticket_belongs_to_another_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = Ticket::factory()->recycle($other)->create();

        $this->actingAsApiUser($owner)
            ->deleteJson("/api/v1/users/{$owner->id}/tickets/{$ticket->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Ticket Not Found');
    }

    public function test_nested_destroy_returns_403_when_user_cannot_delete_the_ticket(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();
        $ticket = Ticket::factory()->recycle($author)->create();

        $this->actingAsApiUser($user)
            ->deleteJson("/api/v1/users/{$author->id}/tickets/{$ticket->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to Delete that resource.');

        $this->assertModelExists($ticket);
    }

    public function test_nested_destroy_returns_404_for_missing_tickets(): void
    {
        $owner = User::factory()->create();

        $this->actingAsApiUser($owner)
            ->deleteJson("/api/v1/users/{$owner->id}/tickets/999999")
            ->assertNotFound()
            ->assertJsonPath('message', 'Ticket Not Found');
    }
}
