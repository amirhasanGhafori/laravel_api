<?php

namespace Modules\Ticket\database\factories;

use Modules\Ticket\Models\Ticket;
use Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'=>User::factory(),
            'title'=>fake()->words(3,true),
            'description'=>fake()->paragraph(),
            'status'=>fake()->randomElement(['A','C','H','X'])
        ];
    }
}
