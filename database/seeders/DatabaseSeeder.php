<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ImportClub;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'username' => 'admin',
        ], [
            'name' => 'Admin',
            'surname' => 'Club',
            'email' => 'admin@local.test',
            'password' => Hash::make('admin'),
            'role' => UserRole::ADMIN,
            'active' => true,
        ]);

        Season::updateOrCreate([
            'name' => '2025-2026',
        ], [
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'active' => true,
            'notes' => 'Temporada inicial',
        ]);

        ImportClub::updateOrCreate([
            'fbib_club_id' => 120,
        ], [
            'name' => 'Club importació inicial',
            'url' => 'https://www.fbib.es/club/120',
            'active' => true,
        ]);
    }
}
