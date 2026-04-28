<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cyberguard.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin123'),
                'role' => 'ADMIN', // si tu as une colonne role
            ]
        );
    }
}

