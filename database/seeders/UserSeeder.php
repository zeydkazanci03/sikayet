<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::create([
            'name' => 'Süper Admin',
            'email' => 'superadmin@sikayetvar.com',
            'password' => Hash::make('SuperAdmin123!'),
            'user_type' => 'admin',
            'phone' => '05551234567',
            'phone_verified' => true,
            'gender' => 'male',
            'city' => 'İstanbul',
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super-admin');

        // Admin
        $admin = User::create([
            'name' => 'Admin Kullanıcı',
            'email' => 'admin@sikayetvar.com',
            'password' => Hash::make('Admin123!'),
            'user_type' => 'admin',
            'phone' => '05551234568',
            'phone_verified' => true,
            'gender' => 'female',
            'city' => 'Ankara',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Moderator
        $moderator = User::create([
            'name' => 'Moderatör',
            'email' => 'moderator@sikayetvar.com',
            'password' => Hash::make('Moderator123!'),
            'user_type' => 'admin',
            'phone' => '05551234569',
            'phone_verified' => true,
            'gender' => 'male',
            'city' => 'İzmir',
            'is_active' => true,
        ]);
        $moderator->assignRole('moderator');

        // Test Customer User
        User::create([
            'name' => 'Ahmet Yılmaz',
            'email' => 'customer@test.com',
            'password' => Hash::make('Customer123!'),
            'user_type' => 'customer',
            'phone' => '05551234570',
            'phone_verified' => true,
            'birth_date' => '1990-05-15',
            'gender' => 'male',
            'city' => 'İstanbul',
            'address' => 'Test Mahallesi, Test Caddesi No: 123',
            'is_active' => true,
        ]);

        // Test Brand User
        User::create([
            'name' => 'Ayşe Demir',
            'email' => 'brand@test.com',
            'password' => Hash::make('Brand123!'),
            'user_type' => 'brand',
            'phone' => '05551234571',
            'phone_verified' => true,
            'gender' => 'female',
            'city' => 'Ankara',
            'is_active' => true,
        ]);
    }
}
