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

        UserRole::where('role', 'analyst')->delete();
        User::where('email', 'analyst@univ.dz')->delete();

        // Admin démonstration
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

        // Compte mini site démonstration
        $miniSiteUser = User::firstOrCreate(
            ['email' => 'metier@gmail.com'],
            [
                'nom' => 'Compte Mini Site',
                'password' => Hash::make('Metier@123'),
                'is_active' => true,
                'uuid' => Str::uuid(),
                'created_at' => $now,
            ]
        );
        UserRole::where('user_id', $miniSiteUser->id)
            ->where('role', '!=', AppRole::Admin->value)
            ->delete();

        $this->command->info('✅ Comptes de démonstration prêts :');
        $this->command->line('   - Admin   : admin@gmail.com / Admin@123');
        $this->command->line('   - Mini site : metier@gmail.com / Metier@123');
    }
}
