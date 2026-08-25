<?php

namespace Tests\Feature\Api\V1;

use App\Models\Ticket;
use App\Models\User;
use Tests\Feature\Api\ApiTestCase;

class TicketTest extends ApiTestCase
{
    public function test_public_tickets_index_returns_paginated_collection_without_authentication(): void
    {
        Ticket::factory()->count(2)->create();

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonStructure($this->ticketCollectionStructure())
            ->assertJsonCount(2, 'data');
    }

    public function test_public_tickets_index_returns_empty_collection(): void
    {
        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_v1_index_returns_paginated_tickets_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        Ticket::factory()->count(2)->create();

        $this->actingAsApiUser($user)
            ->getJson('/api/v1/tickets')
            ->assertOk()
            ->assertJsonStructure($this->ticketCollectionStructure())
            ->assertJsonCount(2, 'data');
    }

    public function test_v1_index_returns_empty_collection(): void
    {
        $user = User::factory()->create();

        $this->actingAsApiUser($user)
            ->getJson('/api/v1/tickets')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_v1_index_paginates_beyond_the_first_page(): void
    {
        $user = User::factory()->create();
        Ticket::factory()->count(16)->create();

        $this->actingAsApiUser($user)
            ->getJson('/api/v1/tickets?page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_v1_index_filters_tickets_by_status(): void
    {
        $user = User::factory()->create();
        Ticket::factory()->create(['status' => 'A']);
        Ticket::factory()->create(['status' => 'C']);

        $this->actingAsApiUser($user)
            ->getJson('/api/v1/tickets?status=A')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.status', 'A');
    }

    public function test_v1_index_includes_author_when_include_user_is_requested(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAsApiUser($user)
            ->getJson('/api/v1/tickets?include=user')
            ->assertOk()
            ->assertJsonPath('data.0.includes.id', $ticket->user_id);
    }

    public function test_show_returns_a_ticket_resource(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAsApiUser($user)
            ->getJson("/api/v1/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonStructure($this->ticketResourceStructure())
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.attributes.title', $ticket->title)
            ->assertJsonPath('data.attributes.description', $ticket->description);
    }

    public function test_show_includes_author_when_requested(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAsApiUser($user)
            ->getJson("/api/v1/tickets/{$ticket->id}?include=author")
            ->assertOk()
            ->assertJsonPath('data.includes.id', $ticket->user_id)
            ->assertJsonPath('data.includes.attributes.email', $ticket->user->email);
    }

    public function test_show_returns_404_for_missing_tickets(): void
    {
        $user = User::factory()->create();

        $this->actingAsApiUser($user)
            ->getJson('/api/v1/tickets/999999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Ticket Not Found.')
            ->assertJsonPath('status', 404);
    }

    public function test_manager_can_store_a_ticket(): void
    {
        $manager = User::factory()->manager()->create();
        $author = User::factory()->create();

        $response = $this->actingAsApiUser($manager)
            ->postJson('/api/v1/tickets', $this->ticketPayload($author->id));

        $response
            ->assertStatus(201)
            ->assertJsonStructure($this->ticketResourceStructure())
            ->assertJsonPath('data.attributes.title', 'Test ticket')
            ->assertJsonPath('data.relationship.author.data.id', $author->id);

        $ticket = Ticket::query()->first();
        $this->assertNotNull($ticket);
        $this->assertSame('Test ticket', $ticket->title);
        $this->assertSame($author->id, $ticket->user_id);
    }

    public function test_non_manager_cannot_store_a_ticket(): void
    {
        $user = User::factory()->create();

        $this->actingAsApiUser($user)
            ->postJson('/api/v1/tickets', $this->ticketPayload($user->id))
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to store that resource.')
            ->assertJsonPath('status', 403);

        $this->assertSame(0, Ticket::query()->count());
    }

    public function test_store_returns_422_when_required_fields_are_missing(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAsApiUser($manager)
            ->postJson('/api/v1/tickets', ['data' => ['attributes' => []]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'data.attributes.title',
                'data.attributes.description',
                'data.attributes.status',
                'data.relationships.author.data.id',
            ]);
    }

    public function test_store_returns_422_when_status_is_invalid(): void
    {
        $manager = User::factory()->manager()->create();
        $author = User::factory()->create();

        $this->actingAsApiUser($manager)
            ->postJson('/api/v1/tickets', $this->ticketPayload($author->id, [
                'data' => ['attributes' => ['status' => 'Z']],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data.attributes.status']);
    }

    public function test_store_returns_422_when_author_does_not_exist(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAsApiUser($manager)
            ->postJson('/api/v1/tickets', $this->ticketPayload(999999))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data.relationships.author.data.id']);
    }

    public function test_manager_can_update_any_ticket(): void
    {
        $manager = User::factory()->manager()->create();
        $ticket = Ticket::factory()->create(['title' => 'Old title']);

        $this->actingAsApiUser($manager)
            ->patchJson("/api/v1/tickets/{$ticket->id}", [
                'data' => ['attributes' => ['title' => 'New title']],
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.title', 'New title');

        $this->assertSame('New title', $ticket->refresh()->title);
    }

    public function test_owner_can_update_their_own_ticket(): void
    {
        $owner = User::factory()->create();
        $ticket = Ticket::factory()->recycle($owner)->create();

        $this->actingAsApiUser($owner)
            ->patchJson("/api/v1/tickets/{$ticket->id}", [
                'data' => ['attributes' => ['title' => 'Owned update']],
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.title', 'Owned update');
    }

    public function test_non_owner_cannot_update_another_users_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAsApiUser($user)
            ->patchJson("/api/v1/tickets/{$ticket->id}", [
                'data' => ['attributes' => ['title' => 'Nope']],
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to update that resource.');
    }

    public function test_owner_cannot_change_author_on_update(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = Ticket::factory()->recycle($owner)->create();

        $this->actingAsApiUser($owner)
            ->patchJson("/api/v1/tickets/{$ticket->id}", [
                'data' => [
                    'relationships' => [
                        'author' => ['data' => ['id' => $other->id]],
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data.relationships.author.data.id']);
    }

    public function test_update_returns_422_when_status_is_invalid(): void
    {
        $manager = User::factory()->manager()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAsApiUser($manager)
            ->patchJson("/api/v1/tickets/{$ticket->id}", [
                'data' => ['attributes' => ['status' => 'bad']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data.attributes.status']);
    }

    public function test_update_returns_404_for_missing_tickets(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAsApiUser($manager)
            ->patchJson('/api/v1/tickets/999999', [
                'data' => ['attributes' => ['title' => 'Nope']],
            ])
            ->assertNotFound();
    }

    public function test_manager_can_replace_a_ticket(): void
    {
        $manager = User::factory()->manager()->create();
        $author = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAsApiUser($manager)
            ->putJson("/api/v1/tickets/{$ticket->id}", $this->ticketPayload($author->id, [
                'data' => ['attributes' => ['title' => 'Replaced', 'status' => 'C']],
            ]))
            ->assertOk()
            ->assertJsonPath('data.attributes.title', 'Replaced')
            ->assertJsonPath('data.attributes.status', 'C')
            ->assertJsonPath('data.relationship.author.data.id', $author->id);
    }

    public function test_non_manager_can_replace_any_ticket_because_replace_does_not_check_ownership(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();
        $author = User::factory()->create();

        $this->actingAsApiUser($user)
            ->putJson("/api/v1/tickets/{$ticket->id}", $this->ticketPayload($author->id, [
                'data' => ['attributes' => ['title' => 'Agent replace']],
            ]))
            ->assertOk()
            ->assertJsonPath('data.attributes.title', 'Agent replace');
    }

    public function test_replace_returns_403_when_token_lacks_replace_ability(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->recycle($user)->create();

        $this->actingAsApiUser($user, [])
            ->putJson("/api/v1/tickets/{$ticket->id}", $this->ticketPayload($user->id))
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to Replace that resource.');
    }

    public function test_replace_returns_422_when_required_fields_are_missing(): void
    {
        $manager = User::factory()->manager()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAsApiUser($manager)
            ->putJson("/api/v1/tickets/{$ticket->id}", ['data' => ['attributes' => []]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'data.attributes.title',
                'data.attributes.description',
                'data.attributes.status',
                'data.relationships.author.data.id',
            ]);
    }

    public function test_replace_returns_404_for_missing_tickets(): void
    {
        $manager = User::factory()->manager()->create();
        $author = User::factory()->create();

        $this->actingAsApiUser($manager)
            ->putJson('/api/v1/tickets/999999', $this->ticketPayload($author->id))
            ->assertNotFound()
            ->assertJsonPath('message', 'Ticket Not Found');
    }

    public function test_manager_can_delete_any_ticket(): void
    {
        $manager = User::factory()->manager()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAsApiUser($manager)
            ->deleteJson("/api/v1/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Ticket Successfully Delete');

        $this->assertModelMissing($ticket);
    }

    public function test_owner_can_delete_their_own_ticket(): void
    {
        $owner = User::factory()->create();
        $ticket = Ticket::factory()->recycle($owner)->create();

        $this->actingAsApiUser($owner)
            ->deleteJson("/api/v1/tickets/{$ticket->id}")
            ->assertOk();

        $this->assertModelMissing($ticket);
    }

    public function test_non_owner_cannot_delete_another_users_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAsApiUser($user)
            ->deleteJson("/api/v1/tickets/{$ticket->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to Delete that resource.');

        $this->assertModelExists($ticket);
    }

    public function test_destroy_returns_404_for_missing_tickets(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAsApiUser($manager)
            ->deleteJson('/api/v1/tickets/999999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Ticket not found.');
    }
}
