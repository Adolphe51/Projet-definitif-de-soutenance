<?php

namespace Database\Seeders;

use App\Enums\AppRole;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SystemSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'nom' => 'Admin Super',
                'password' => Hash::make('Admin@123'),
                'is_active' => true,
                'uuid' => Str::uuid(),
                'created_at' => $now,
            ]
        );
        UserRole::firstOrCreate([
            'user_id' => $admin->id,
            'role' => AppRole::Admin->value,
        ]);

        // Secrétaire
        $analyst = User::firstOrCreate(
            ['email' => 'analyst@univ.dz'],
            [
                'nom' => 'Benali Fatima',
                'password' => Hash::make('Secret@123'),
                'is_active' => true,
                'uuid' => Str::uuid(),
                'created_at' => $now,
            ]
        );
        UserRole::firstOrCreate([
            'user_id' => $analyst->id,
            'role' => AppRole::Analyst->value,
        ]);

        $this->command->info('✅ 2 utilisateurs créés (admin, analyst)');
    }
}
