<?php

namespace Database\Factories;

use App\Models\ImportClub;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'import_club_id' => ImportClub::factory(),
            'name' => 'Equip '.fake()->unique()->word(),
            'display_name' => fake()->company(),
            'fbib_team_id' => fake()->unique()->numberBetween(1, 99999),
            'category' => fake()->randomElement(['Mini', 'Cadet', 'Junior', 'Senior']),
            'sponsor' => fake()->company(),
            'competition_group' => fake()->bothify('G-##'),
            'gender' => fake()->randomElement(['M', 'F', 'Mixte']),
            'level' => fake()->randomElement(['Preferent', 'Insular', 'Autonòmic']),
            'active' => true,
            'is_imported' => true,
            'url_fbib' => 'https://www.fbib.es/team/'.fake()->numberBetween(1, 99999),
            'import_data' => [],
            'notes' => fake()->sentence(),
        ];
    }
}
