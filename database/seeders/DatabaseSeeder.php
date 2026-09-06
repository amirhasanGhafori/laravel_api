<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Login;
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


        $users = User::factory(100000)->recycle($companies)->create();



        $logins = Login::factory(90000)
            ->recycle($users)
            ->create();

        Ticket::factory(100)->recycle($users)->create();

        User::factory()->create([
            'name' => 'amirhasan',
            'email' => 'amirhasan@gmail.com',
            'password' => '13771120',
            'is_manager' => true
        ]);
    }
}
