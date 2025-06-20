<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 3 test users
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@ex.com',
            'role' => 'admin',
            'phone' => '123456789',
            'password' => Hash::make('123')
        ]);

        User::factory()->create([
            'name' => 'Test User 1',
            'email' => 'user1@ex.com',
            'phone' => '123456789',
            'role' => 'user',
            'password' => Hash::make('123')
        ]);

        User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'user2@ex.com',
            'phone' => '123456789',
            'role' => 'user',
            'password' => Hash::make('123')
        ]);
        
        // Seed products
        // $this->call([
        //     ProductSeeder::class,
        // ]);
    }
}
