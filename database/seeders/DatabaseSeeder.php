<?php

namespace Database\Seeders;

use Modules\Ticket\Models\Ticket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Company\Models\Company;
use Modules\Post\Models\Post;
use Modules\User\Models\Login;
use Modules\User\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $companies = Company::factory(500)->create();


        $users = User::factory(1000)->recycle($companies)->create();



        Login::factory(1500)
            ->recycle($users)
            ->create();

        Ticket::factory(100)->recycle($users)->create();

        Post::factory(100)->recycle($users)->create();
    }
}
