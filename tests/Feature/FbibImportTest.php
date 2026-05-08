<?php

namespace Tests\Feature;

use App\Models\ClubMatch;
use App\Models\ImportClub;
use App\Models\Season;
use App\Models\Team;
use App\Services\Imports\FbibApiClient;
use App\Services\Imports\FbibImportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Fakes\FakeFbibApiClient;
use Tests\TestCase;

class FbibImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_import_creates_records_from_fbib_api_source(): void
    {
        $season = Season::factory()->create(['active' => true]);
        $club = ImportClub::factory()->create(['fbib_club_id' => 120, 'url' => 'https://www.fbib.es/club/120']);

        $this->app->instance(FbibApiClient::class, new FakeFbibApiClient(
            clubSheets: [
                120 => [
                    'categories' => [
                        [
                            'name' => 'Junior Preferent',
                            'teams' => [
                                [
                                    'idSignedTeam' => 10,
                                    'name' => 'CB La Salle A',
                                    'categoriesRegistredName' => 'Junior Preferent',
                                    'registredGroups' => 'G-1',
                                    'gender' => 'M',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ));

        app(FbibImportService::class)->importTeams($club);

        $this->assertDatabaseHas('teams', [
            'season_id' => $season->id,
            'fbib_team_id' => 10,
            'name' => 'CB La Salle A',
        ]);
    }

    public function test_match_import_inserts_all_matches_even_without_past_results(): void
    {
        Carbon::setTestNow('2026-04-27 12:00:00');

        $season = Season::factory()->create(['active' => true]);
        $club = ImportClub::factory()->create(['fbib_club_id' => 120, 'url' => 'https://www.fbib.es/club/120']);
        Team::factory()->create([
            'season_id' => $season->id,
            'import_club_id' => $club->id,
            'fbib_team_id' => 10,
        ]);

        $this->app->instance(FbibApiClient::class, new FakeFbibApiClient(
            teamMatches: [
                10 => [
                    [
                        'idMatch' => 1001,
                        'nameLocalTeam' => 'CB La Salle A',
                        'nameVisitorTeam' => 'Rival Futur',
                        'matchDay' => '2026-05-10 18:30:00',
                        'hourMatch' => '18:30',
                        'numMatchDay' => 12,
                        'nameField' => 'Palau',
                        'state' => '1',
                    ],
                    [
                        'idMatch' => 1002,
                        'nameLocalTeam' => 'CB La Salle A',
                        'nameVisitorTeam' => 'Rival Sense Resultat',
                        'matchDay' => '2026-04-10 18:30:00',
                        'hourMatch' => '18:30',
                        'numMatchDay' => 10,
                        'nameField' => 'Palau',
                        'state' => '1',
                    ],
                    [
                        'idMatch' => 1003,
                        'nameLocalTeam' => 'CB La Salle A',
                        'nameVisitorTeam' => 'Rival Ja Jugat',
                        'matchDay' => '2026-04-05 17:00:00',
                        'hourMatch' => '17:00',
                        'numMatchDay' => 9,
                        'nameField' => 'Palau',
                        'state' => '2',
                        'localScore' => 72,
                        'visitorScore' => 66,
                    ],
                ],
            ],
        ));

        app(FbibImportService::class)->importMatches($club);

        $this->assertSame(3, ClubMatch::count());

        $futureMatch = ClubMatch::query()->where('fbib_match_id', 1001)->firstOrFail();
        $this->assertSame('2026-05-10', $futureMatch->match_date?->toDateString());
        $this->assertSame('18:30', $futureMatch->match_time);
        $this->assertSame('2026-05-10 18:30:00', $futureMatch->match_datetime?->format('Y-m-d H:i:s'));
        $this->assertSame('G-1', $futureMatch->competition);
        $this->assertNull($futureMatch->score_home);
        $this->assertNull($futureMatch->score_away);

        $this->assertDatabaseHas('matches', [
            'fbib_match_id' => 1003,
            'score_home' => 72,
            'score_away' => 66,
            'status' => 'FINALITZAT',
        ]);

        $pastMatchWithoutScore = ClubMatch::query()->where('fbib_match_id', 1002)->firstOrFail();
        $this->assertSame('2026-04-10', $pastMatchWithoutScore->match_date?->toDateString());
        $this->assertSame('18:30', $pastMatchWithoutScore->match_time);
        $this->assertNull($pastMatchWithoutScore->score_home);
        $this->assertNull($pastMatchWithoutScore->score_away);

        Carbon::setTestNow();
    }

    public function test_match_import_does_not_set_time_when_future_time_is_missing(): void
    {
        Carbon::setTestNow('2026-04-27 12:00:00');

        $season = Season::factory()->create(['active' => true]);
        $club = ImportClub::factory()->create(['fbib_club_id' => 120, 'url' => 'https://www.fbib.es/club/120']);
        Team::factory()->create([
            'season_id' => $season->id,
            'import_club_id' => $club->id,
            'fbib_team_id' => 10,
        ]);

        $this->app->instance(FbibApiClient::class, new FakeFbibApiClient(
            teamMatches: [
                10 => [
                    [
                        'idMatch' => 1004,
                        'nameLocalTeam' => 'CB La Salle A',
                        'nameVisitorTeam' => 'Rival Pendent Hora',
                        'matchDay' => '2026-05-12 00:00:00',
                        'numMatchDay' => 13,
                        'nameField' => 'Palau',
                        'state' => '1',
                    ],
                ],
            ],
        ));

        app(FbibImportService::class)->importMatches($club);

        $match = ClubMatch::query()->where('fbib_match_id', 1004)->firstOrFail();

        $this->assertSame('2026-05-12', $match->match_date?->toDateString());
        $this->assertNull($match->match_time);
        $this->assertNull($match->match_datetime);

        Carbon::setTestNow();
    }

    public function test_match_import_only_updates_date_time_and_result_for_existing_matches(): void
    {
        $season = Season::factory()->create(['active' => true]);
        $club = ImportClub::factory()->create(['fbib_club_id' => 120]);
        $team = Team::factory()->create([
            'season_id' => $season->id,
            'import_club_id' => $club->id,
            'fbib_team_id' => 10,
        ]);

        $match = ClubMatch::create([
            'season_id' => $season->id,
            'team_id' => $team->id,
            'fbib_match_id' => 1005,
            'home_team' => 'CB La Salle A',
            'away_team' => 'Rival Club',
            'competition' => 'Preferent',
            'match_date' => '2026-05-10',
            'match_time' => '18:30',
            'match_datetime' => '2026-05-10 18:30:00',
            'pavilion' => 'Pavello Original',
            'round' => '7',
            'score_home' => null,
            'score_away' => null,
            'notes' => 'No tocar',
            'import_data' => [
                '_imported_values' => [
                    'match_date' => '2026-05-10',
                    'match_time' => '18:30',
                    'match_datetime' => '2026-05-10 18:30:00',
                    'competition' => 'Preferent',
                    'status' => null,
                    'score_home' => null,
                    'score_away' => null,
                ],
            ],
        ]);

        $this->app->instance(FbibApiClient::class, new FakeFbibApiClient(
            teamMatches: [
                10 => [
                    [
                        'idMatch' => 1005,
                        'nameLocalTeam' => 'CB La Salle A',
                        'nameVisitorTeam' => 'Rival Club',
                        'matchDay' => '2026-05-11 20:00:00',
                        'hourMatch' => '20:00',
                        'numMatchDay' => 9,
                        'nameField' => 'Pavello Nou',
                        'state' => '2',
                        'localScore' => 81,
                        'visitorScore' => 77,
                    ],
                ],
            ],
        ));

        app(FbibImportService::class)->importMatches($club);

        $match->refresh();

        $this->assertSame('2026-05-11', $match->match_date?->toDateString());
        $this->assertSame('20:00', $match->match_time);
        $this->assertSame('2026-05-11 20:00:00', $match->match_datetime?->format('Y-m-d H:i:s'));
        $this->assertSame('G-1', $match->competition);
        $this->assertSame(81, $match->score_home);
        $this->assertSame(77, $match->score_away);
        $this->assertSame('FINALITZAT', $match->status);
        $this->assertSame('Pavello Original', $match->pavilion);
        $this->assertSame('7', $match->round);
        $this->assertSame('No tocar', $match->notes);
    }

    public function test_match_import_preserves_manual_schedule_and_result_changes(): void
    {
        $season = Season::factory()->create(['active' => true]);
        $club = ImportClub::factory()->create(['fbib_club_id' => 120]);
        $team = Team::factory()->create([
            'season_id' => $season->id,
            'import_club_id' => $club->id,
            'fbib_team_id' => 10,
        ]);

        $match = ClubMatch::create([
            'season_id' => $season->id,
            'team_id' => $team->id,
            'fbib_match_id' => 1006,
            'home_team' => 'CB La Salle A',
            'away_team' => 'Rival Manual',
            'competition' => 'Autonomic',
            'match_date' => '2026-05-10',
            'match_time' => '18:30',
            'match_datetime' => '2026-05-10 18:30:00',
            'status' => 'PROGRAMAT',
            'score_home' => null,
            'score_away' => null,
            'import_data' => [
                '_imported_values' => [
                    'match_date' => '2026-05-10',
                    'match_time' => '18:30',
                    'match_datetime' => '2026-05-10 18:30:00',
                    'competition' => 'Autonomic',
                    'status' => 'PROGRAMAT',
                    'score_home' => null,
                    'score_away' => null,
                ],
            ],
        ]);

        $match->update([
            'competition' => 'Canviada manualment',
            'match_date' => '2026-05-12',
            'match_time' => '20:15',
            'match_datetime' => '2026-05-12 20:15:00',
            'status' => 'CANVIAT MANUALMENT',
            'score_home' => 90,
            'score_away' => 80,
        ]);

        $this->app->instance(FbibApiClient::class, new FakeFbibApiClient(
            teamMatches: [
                10 => [
                    [
                        'idMatch' => 1006,
                        'nameLocalTeam' => 'CB La Salle A',
                        'nameVisitorTeam' => 'Rival Manual',
                        'matchDay' => '2026-05-14 17:00:00',
                        'hourMatch' => '17:00',
                        'numMatchDay' => 10,
                        'nameField' => 'Palau',
                        'state' => '2',
                        'localScore' => 77,
                        'visitorScore' => 70,
                    ],
                ],
            ],
        ));

        app(FbibImportService::class)->importMatches($club);

        $match->refresh();

        $this->assertSame('2026-05-12', $match->match_date?->toDateString());
        $this->assertSame('20:15', $match->formatted_match_time);
        $this->assertSame('2026-05-12 20:15:00', $match->match_datetime?->format('Y-m-d H:i:s'));
        $this->assertSame('Canviada manualment', $match->competition);
        $this->assertSame('CANVIAT MANUALMENT', $match->status);
        $this->assertSame(90, $match->score_home);
        $this->assertSame(80, $match->score_away);
    }
}
