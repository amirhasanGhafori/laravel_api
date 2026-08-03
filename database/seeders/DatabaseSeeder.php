<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(10)->create();

        Ticket::factory(100)->recycle($users)->create();

        User::factory()->create([
            'name' => 'amirhasan',
            'email' => 'amirhasan@gmail.com',
            'password'=>'13771120',
            'is_manager'=>true
        ]);
    }
}
