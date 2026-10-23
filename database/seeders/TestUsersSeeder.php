<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Administrador', 'email' => 'admin@eventportal.test', 'role' => UserRole::Admin],
            ['name' => 'Vendedor', 'email' => 'vendedor@eventportal.test', 'role' => UserRole::Seller],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
