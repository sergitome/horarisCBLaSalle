<?php

namespace Database\Factories;

use App\Models\ImportClub;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportClubFactory extends Factory
{
    protected $model = ImportClub::class;

    public function definition(): array
    {
        $id = fake()->unique()->numberBetween(100, 9999);

        return [
            'name' => 'Club '.$id,
            'fbib_club_id' => $id,
            'url' => 'https://www.fbib.es/club/'.$id,
            'active' => true,
        ];
    }
}
