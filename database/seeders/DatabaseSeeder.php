<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Login;
use App\Models\Post;
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
        $companies = Company::factory(6000)->create();


        $users = User::factory(20000)->recycle($companies)->create();



        Login::factory(25000)
            ->recycle($users)
            ->create();

        Ticket::factory(100)->recycle($users)->create();

        Post::factory(30000)->recycle($users)->create();
    }
}
