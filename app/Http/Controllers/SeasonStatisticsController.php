<?php

namespace App\Http\Controllers;

use App\Models\ClubMatch;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SeasonStatisticsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ClubMatch::class);

        $seasons = Season::query()->orderByDesc('start_date')->get();
        $selectedSeason = $this->resolveSeason($request, $seasons);
        $seasonTeams = $selectedSeason
            ? Team::query()
                ->with('importClub')
                ->where('season_id', $selectedSeason->id)
                ->orderBy('category')
                ->orderBy('display_name')
                ->orderBy('name')
                ->get()
            : collect();
        $selectedTeam = $this->resolveTeam($request, $seasonTeams);

        [$teamStats, $clubStats, $competitionStats, $summary] = $selectedSeason
            ? $this->buildSeasonStatistics($selectedSeason, $selectedTeam)
            : [collect(), collect(), collect(), [
                'teams_count' => 0,
                'clubs_count' => 0,
                'competitions_count' => 0,
                'played_matches' => 0,
                'pending_matches' => 0,
                'selected_season_name' => null,
                'selected_team_name' => null,
            ]];

        return view('statistics.index', [
            'seasons' => $seasons,
            'seasonTeams' => $seasonTeams,
            'selectedSeason' => $selectedSeason,
            'selectedTeam' => $selectedTeam,
            'teamStats' => $teamStats,
            'clubStats' => $clubStats,
            'competitionStats' => $competitionStats,
            'summary' => $summary,
        ]);
    }

    private function resolveSeason(Request $request, Collection $seasons): ?Season
    {
        if ($request->filled('season_id')) {
            return $seasons->firstWhere('id', (int) $request->integer('season_id'));
        }

        return $seasons->firstWhere('active', true) ?? $seasons->first();
    }

    private function resolveTeam(Request $request, Collection $teams): ?Team
    {
        if (! $request->filled('team_id')) {
            return null;
        }

        return $teams->firstWhere('id', (int) $request->integer('team_id'));
    }

    private function buildSeasonStatistics(Season $season, ?Team $selectedTeam = null): array
    {
        $matchesQuery = ClubMatch::query()
            ->with('team.importClub')
            ->where('season_id', $season->id);

        if ($selectedTeam) {
            $matchesQuery->where('team_id', $selectedTeam->id);
        }

        $matches = $matchesQuery->get();

        $teamStats = $matches
            ->groupBy('team_id')
            ->map(function (Collection $teamMatches) {
                $team = $teamMatches->first()->team;
                $stats = $this->emptyStats();

                foreach ($teamMatches as $match) {
                    $this->applyMatchToStats($stats, $match, $team?->display_name ?: $team?->name);
                }

                return [
                    'team_id' => $team?->id,
                    'team_name' => $team?->display_name ?: $team?->name ?: 'Equip sense nom',
                    'category' => $team?->category,
                    'club_name' => $team?->importClub?->name ?: 'Sense club',
                    'club_id' => $team?->import_club_id,
                    'stats' => $this->appendAverages($stats),
                ];
            })
            ->sortBy([
                ['club_name', 'asc'],
                ['category', 'asc'],
                ['team_name', 'asc'],
            ])
            ->values();

        $clubStats = $teamStats
            ->groupBy(fn (array $row) => $row['club_id'] ?? 'no-club')
            ->map(function (Collection $rows) {
                $stats = $this->emptyStats();

                foreach ($rows as $row) {
                    foreach (array_keys($stats) as $key) {
                        $stats[$key] += $row['stats'][$key];
                    }
                }

                return [
                    'club_id' => $rows->first()['club_id'],
                    'club_name' => $rows->first()['club_name'],
                    'teams_count' => $rows->count(),
                    'stats' => $this->appendAverages($stats),
                ];
            })
            ->sortBy('club_name')
            ->values();

        $competitionStats = $matches
            ->groupBy(fn (ClubMatch $match) => $match->competition ?: 'Sense competicio')
            ->map(function (Collection $competitionMatches, string $competitionName) {
                $stats = $this->emptyStats();

                foreach ($competitionMatches as $match) {
                    $teamName = $match->team?->display_name ?: $match->team?->name;
                    $this->applyMatchToStats($stats, $match, $teamName);
                }

                return [
                    'competition_name' => $competitionName,
                    'teams_count' => $competitionMatches->pluck('team_id')->filter()->unique()->count(),
                    'stats' => $this->appendAverages($stats),
                ];
            })
            ->sortBy('competition_name')
            ->values();

        return [
            $teamStats,
            $clubStats,
            $competitionStats,
            [
                'teams_count' => $teamStats->count(),
                'clubs_count' => $clubStats->count(),
                'competitions_count' => $competitionStats->count(),
                'played_matches' => $matches->filter(fn (ClubMatch $match) => $this->isPlayedMatch($match))->count(),
                'pending_matches' => $matches->filter(fn (ClubMatch $match) => ! $this->isPlayedMatch($match))->count(),
                'selected_season_name' => $season->name,
                'selected_team_name' => $selectedTeam?->display_name ?: $selectedTeam?->name,
            ],
        ];
    }

    private function applyMatchToStats(array &$stats, ClubMatch $match, ?string $teamName): void
    {
        $normalizedTeamName = $this->normalizeName($teamName);
        $isHome = $normalizedTeamName !== '' && $normalizedTeamName === $this->normalizeName($match->home_team);
        $isAway = $normalizedTeamName !== '' && $normalizedTeamName === $this->normalizeName($match->away_team);

        if (! $isHome && ! $isAway) {
            return;
        }

        $context = $isHome ? 'home' : 'away';

        if (! $this->isPlayedMatch($match)) {
            $stats['pending']++;
            $stats["{$context}_pending"]++;

            return;
        }

        $stats['played']++;
        $stats["{$context}_played"]++;

        $pointsFor = $isHome ? (int) $match->score_home : (int) $match->score_away;
        $pointsAgainst = $isHome ? (int) $match->score_away : (int) $match->score_home;

        $stats['points_for'] += $pointsFor;
        $stats['points_against'] += $pointsAgainst;
        $stats["{$context}_points_for"] += $pointsFor;
        $stats["{$context}_points_against"] += $pointsAgainst;

        if ($match->score_home === $match->score_away) {
            return;
        }

        $won = $isHome
            ? $match->score_home > $match->score_away
            : $match->score_away > $match->score_home;

        if ($isHome) {
            $stats[$won ? 'home_wins' : 'home_losses']++;
        }

        if ($isAway) {
            $stats[$won ? 'away_wins' : 'away_losses']++;
        }

        $stats[$won ? 'total_wins' : 'total_losses']++;
    }

    private function emptyStats(): array
    {
        return [
            'home_wins' => 0,
            'home_losses' => 0,
            'home_played' => 0,
            'home_pending' => 0,
            'home_points_for' => 0,
            'home_points_against' => 0,
            'away_wins' => 0,
            'away_losses' => 0,
            'away_played' => 0,
            'away_pending' => 0,
            'away_points_for' => 0,
            'away_points_against' => 0,
            'total_wins' => 0,
            'total_losses' => 0,
            'played' => 0,
            'pending' => 0,
            'points_for' => 0,
            'points_against' => 0,
        ];
    }

    private function appendAverages(array $stats): array
    {
        $stats['avg_points_for'] = $this->formatAverage($stats['points_for'], $stats['played']);
        $stats['avg_points_against'] = $this->formatAverage($stats['points_against'], $stats['played']);
        $stats['home_avg_points_for'] = $this->formatAverage($stats['home_points_for'], $stats['home_played']);
        $stats['home_avg_points_against'] = $this->formatAverage($stats['home_points_against'], $stats['home_played']);
        $stats['away_avg_points_for'] = $this->formatAverage($stats['away_points_for'], $stats['away_played']);
        $stats['away_avg_points_against'] = $this->formatAverage($stats['away_points_against'], $stats['away_played']);

        return $stats;
    }

    private function formatAverage(int $points, int $played): string
    {
        if ($played === 0) {
            return '-';
        }

        return number_format($points / $played, 1, ',', '.');
    }

    private function isPlayedMatch(ClubMatch $match): bool
    {
        return $match->score_home !== null && $match->score_away !== null;
    }

    private function normalizeName(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));

        return preg_replace('/\s+/', ' ', $value) ?? '';
    }
}
