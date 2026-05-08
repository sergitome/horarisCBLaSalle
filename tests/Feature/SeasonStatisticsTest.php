<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ClubMatch;
use App\Models\ImportClub;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistics_page_groups_results_by_team_and_club_for_a_season(): void
    {
        $season = Season::factory()->create(['name' => '2025-2026', 'active' => true]);
        $otherSeason = Season::factory()->create(['name' => '2024-2025', 'active' => false]);
        $club = ImportClub::factory()->create(['name' => 'CB La Salle']);
        $otherClub = ImportClub::factory()->create(['name' => 'Club Visitant']);

        $teamA = Team::factory()->create([
            'season_id' => $season->id,
            'import_club_id' => $club->id,
            'name' => 'La Salle A',
            'display_name' => 'LA SALLE A',
            'category' => 'Junior',
        ]);

        $teamB = Team::factory()->create([
            'season_id' => $season->id,
            'import_club_id' => $club->id,
            'name' => 'La Salle B',
            'display_name' => 'LA SALLE B',
            'category' => 'Cadet',
        ]);

        $otherSeasonTeam = Team::factory()->create([
            'season_id' => $otherSeason->id,
            'import_club_id' => $otherClub->id,
            'display_name' => 'OTHER TEAM',
        ]);

        ClubMatch::create([
            'season_id' => $season->id,
            'team_id' => $teamA->id,
            'home_team' => 'LA SALLE A',
            'away_team' => 'RIVAL 1',
            'score_home' => 80,
            'score_away' => 70,
        ]);

        ClubMatch::create([
            'season_id' => $season->id,
            'team_id' => $teamA->id,
            'home_team' => 'RIVAL 2',
            'away_team' => 'LA SALLE A',
            'score_home' => 72,
            'score_away' => 75,
        ]);

        ClubMatch::create([
            'season_id' => $season->id,
            'team_id' => $teamA->id,
            'home_team' => 'LA SALLE A',
            'away_team' => 'RIVAL 3',
            'score_home' => 60,
            'score_away' => 68,
        ]);

        ClubMatch::create([
            'season_id' => $season->id,
            'team_id' => $teamB->id,
            'home_team' => 'RIVAL 4',
            'away_team' => 'LA SALLE B',
            'score_home' => 77,
            'score_away' => 70,
        ]);

        ClubMatch::create([
            'season_id' => $season->id,
            'team_id' => $teamB->id,
            'home_team' => 'LA SALLE B',
            'away_team' => 'RIVAL 6',
            'score_home' => null,
            'score_away' => null,
        ]);

        ClubMatch::create([
            'season_id' => $otherSeason->id,
            'team_id' => $otherSeasonTeam->id,
            'home_team' => 'OTHER TEAM',
            'away_team' => 'RIVAL 5',
            'score_home' => 90,
            'score_away' => 40,
        ]);

        $user = User::factory()->create([
            'role' => UserRole::CONSULTA,
            'active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('statistics.index', ['season_id' => $season->id]));

        $response->assertOk();
        $response->assertSee('2025-2026');
        $response->assertSee('CB La Salle');
        $response->assertSee('LA SALLE A');
        $response->assertSee('LA SALLE B');
        $response->assertSee('1G / 1P', false);
        $response->assertSee('1G / 0P', false);
        $response->assertSee('2G / 1P', false);
        $response->assertSee('0G / 1P', false);
        $response->assertSee('Partits pendents');
        $response->assertSee('PF/PC total');
        $response->assertSee('71,7 / 70,0', false);
        $response->assertSee('70,0 / 74,0', false);
        $response->assertDontSee('OTHER TEAM');
    }
}
