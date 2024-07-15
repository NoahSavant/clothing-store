<?php

namespace Database\Seeders;

use App\Constants\UserConstants\UserRole;
use App\Constants\UserConstants\UserStatus;
use App\Models\Collection;
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
            'status' => UserStatus::ACTIVE,
            'image_url' => 'https://res.cloudinary.com/dvcdmxgyk/image/upload/v1720922653/manager_mzptmc.png'
        ]);

        Collection::create([
            'name' => 'Các sản phẩm bán chạy',
            'image_url' => 'https://res.cloudinary.com/dvcdmxgyk/image/upload/v1720346832/files/r98rcmh1t3zilcnijuxv.jpg'
        ]);
    }
}
