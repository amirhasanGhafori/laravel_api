<?php

namespace Tests\Unit;

use App\Models\User;
use App\Permissions\V1\Abilities;
use PHPUnit\Framework\TestCase;

class AbilitiesTest extends TestCase
{
    public function test_it_returns_manager_abilities_when_user_is_a_manager(): void
    {
        $user = new User(['is_manager' => true]);

        $this->assertSame([
            Abilities::CreateTicket,
            Abilities::UpdateTicket,
            Abilities::DeleteTicket,
            Abilities::ReplaceTicket,
            Abilities::CreateUser,
            Abilities::UpdateUser,
            Abilities::DeleteUser,
            Abilities::ReplaceUser,
        ], Abilities::getAbilities($user));
    }

    public function test_it_returns_own_ticket_abilities_when_user_is_not_a_manager(): void
    {
        $user = new User(['is_manager' => false]);

        $this->assertSame([
            Abilities::UpdateOwnTicket,
            Abilities::DeleteOwnTicket,
            Abilities::ReplaceOwnTicket,
        ], Abilities::getAbilities($user));
    }

    public function test_it_does_not_grant_create_own_ticket_to_non_managers(): void
    {
        $user = new User(['is_manager' => false]);

        $this->assertNotContains(Abilities::CreateOwnTicket, Abilities::getAbilities($user));
        $this->assertNotContains(Abilities::CreateTicket, Abilities::getAbilities($user));
    }
}
