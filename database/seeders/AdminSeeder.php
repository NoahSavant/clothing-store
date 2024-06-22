<?php

namespace Database\Seeders;

use App\Constants\UserConstants\UserRole;
use App\Constants\UserConstants\UserStatus;
use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'Admin',
            'email' => 'fashionlineunique@gmail.com',
            'password' => Hash::make('password'), 
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE
        ]);
    }
}
