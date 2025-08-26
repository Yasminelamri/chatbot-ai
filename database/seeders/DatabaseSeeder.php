<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Utilisateurs de test avec rôles
        User::updateOrCreate(
            ['email' => 'alice@example.test'],
            ['name' => 'Alice Beneficiaire', 'password' => Hash::make('password'), 'role' => 'user']
        );
        User::updateOrCreate(
            ['email' => 'team@example.test'],
            ['name' => 'Team Jadara', 'password' => Hash::make('password'), 'role' => 'team']
        );
        User::updateOrCreate(
            ['email' => 'system@example.test'],
            ['name' => 'System Bot', 'password' => Hash::make('password'), 'role' => 'system']
        );
    }
}
