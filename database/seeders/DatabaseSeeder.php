<?php

namespace Database\Seeders;

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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'full_name' => 'Head Counselor',
            'email' => 'aquinorenz69@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('headconselor'),
            'user_type' => \App\Enums\UserRole::ADMIN,
        ]);
    }
}
