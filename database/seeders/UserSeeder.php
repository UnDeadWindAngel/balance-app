<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Test User 1',
            'email' => 'user1@test.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Test User 2',
            'email' => 'user2@test.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Test User 3',
            'email' => 'user3@test.com',
            'password' => Hash::make('password'),
        ]);
    }
}
