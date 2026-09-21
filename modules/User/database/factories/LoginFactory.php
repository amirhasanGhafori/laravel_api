<?php

namespace Modules\User\database\factories;

use Modules\User\Models\Login;
use Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Login>
 */
class LoginFactory extends Factory
{
    protected $model = Login::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ip_address' => fake()->ipv4(),
        ];
    }
}
