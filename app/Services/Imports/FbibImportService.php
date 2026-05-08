<?php

namespace App\Services\Imports;

use App\Models\ClubMatch;
use App\Models\ImportClub;
use App\Models\ImportExecution;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Services\Auditing\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FbibImportService
{
    public function __construct(
        private readonly FbibApiClient $client,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function importTeams(ImportClub $club, ?User $user = null, ?int $executionId = null): ImportExecution
    {
        $execution = $this->startExecution('teams', $club, $executionId);
        $this->become($user);
        $season = Season::query()->where('active', true)->first() ?? Season::latest('start_date')->firstOrFail();

        try {
            $clubSheet = $this->client->fetchClubSheet((int) $club->fbib_club_id);
            $teams = $this->extractTeams($clubSheet);
            $execution->update([
                'result' => 'running',
                'details' => [
                    'phase' => 'processing',
                    'current_team' => null,
                    'processed_teams' => 0,
                    'total_teams' => count($teams),
                    'message' => 'Processant equips importats des de FBIB.',
                ],
            ]);

            foreach ($teams as $index => $payload) {
                $this->updateExecutionProgress($execution, [
                    'phase' => 'processing',
                    'current_team' => $payload['display_name'] ?? $payload['name'],
                    'processed_teams' => $index + 1,
                    'total_teams' => count($teams),
                    'message' => 'Actualitzant equip.',
                ]);

                $team = Team::query()
                    ->where('season_id', $season->id)
                    ->where('fbib_team_id', $payload['fbib_team_id'])
                    ->first();

                $attributes = [
                    'season_id' => $season->id,
                    'import_club_id' => $club->id,
                    'name' => $payload['name'],
                    'display_name' => $payload['display_name'] ?? $payload['name'],
                    'fbib_team_id' => $payload['fbib_team_id'],
                    'category' => $payload['category'],
                    'sponsor' => $payload['sponsor'],
                    'competition_group' => $payload['competition_group'] ?? null,
                    'gender' => $payload['gender'] ?? null,
                    'level' => $payload['level'] ?? null,
                    'active' => true,
                    'is_imported' => true,
                    'url_fbib' => $payload['url'],
                    'import_data' => $payload,
                ];

                if (! $team) {
                    $created = Team::create($attributes);
                    $execution->increment('created_count');
                    $this->auditLogger->logImport('import-team-create', 'Team', $created->id, null, $created->toArray());
                    continue;
                }

                $before = $team->toArray();

                $team->fill(Arr::except($attributes, ['notes', 'import_data']));
                $this->syncImportData($team, $attributes['import_data']);

                if ($team->isDirty()) {
                    $team->save();
                    $execution->increment('updated_count');
                    $this->auditLogger->logImport('import-team-update', 'Team', $team->id, $before, $team->fresh()?->toArray());
                } else {
                    $execution->increment('skipped_count');
                }
            }

            $execution->update([
                'finished_at' => now(),
                'result' => 'success',
                'details' => [
                    'phase' => 'completed',
                    'current_team' => null,
                    'processed_teams' => count($teams),
                    'total_teams' => count($teams),
                    'teams_found' => count($teams),
                    'message' => 'Importacio d\'equips completada.',
                ],
            ]);
        } catch (\Throwable $exception) {
            $execution->increment('error_count');
            $execution->update([
                'finished_at' => now(),
                'result' => 'error',
                'details' => array_merge($execution->details ?? [], [
                    'phase' => 'error',
                    'message' => $exception->getMessage(),
                ]),
            ]);
        }

        return $execution->fresh();
    }

    public function importMatches(ImportClub $club, ?User $user = null, ?int $executionId = null): ImportExecution
    {
        $execution = $this->startExecution('matches', $club, $executionId);
        $this->become($user);

        try {
            $teams = Team::query()->where('import_club_id', $club->id)->get();
            $execution->update([
                'result' => 'running',
                'details' => [
                    'phase' => 'processing',
                    'current_team' => null,
                    'processed_teams' => 0,
                    'total_teams' => $teams->count(),
                    'message' => 'Preparant importacio de partits.',
                ],
            ]);

            foreach ($teams as $index => $team) {
                $this->updateExecutionProgress($execution, [
                    'phase' => 'processing',
                    'current_team' => $team->display_name ?: $team->name,
                    'processed_teams' => $index + 1,
                    'total_teams' => $teams->count(),
                    'message' => 'Important partits de l\'equip.',
                ]);

                $matches = $this->extractMatches($this->client->fetchTeamSeasonMatches((int) $team->fbib_team_id));

                foreach ($matches as $payload) {
                    $match = $this->resolveMatch($team, $payload);

                    $attributes = [
                        'season_id' => $team->season_id,
                        'team_id' => $team->id,
                        'fbib_match_id' => $payload['fbib_match_id'] ?? null,
                        'home_team' => $payload['home_team'],
                        'away_team' => $payload['away_team'],
                        'competition' => $payload['competition'] ?? $team->competition_group,
                        'match_date' => $payload['match_date'],
                        'match_time' => $payload['match_time'],
                        'match_datetime' => $payload['match_datetime'],
                        'round' => $payload['round'],
                        'import_month' => $payload['import_month'],
                        'status' => $payload['status'],
                        'pavilion' => $payload['pavilion'],
                        'score_home' => $payload['score_home'],
                        'score_away' => $payload['score_away'],
                        'is_imported' => true,
                        'import_data' => $payload,
                    ];

                    if (! $match) {
                        $attributes['import_data'] = $this->buildMatchImportData($attributes['import_data'], $attributes);
                        $created = ClubMatch::create($attributes);
                        $execution->increment('created_count');
                        $this->auditLogger->logImport('import-match-create', 'ClubMatch', $created->id, null, $created->toArray());
                        continue;
                    }

                    $before = $match->toArray();
                    $this->applyImportedMatchUpdates($match, $attributes);

                    if ($match->isDirty()) {
                        $match->save();
                        $execution->increment('updated_count');
                        $this->auditLogger->logImport('import-match-update', 'ClubMatch', $match->id, $before, $match->fresh()?->toArray());
                    } else {
                        $execution->increment('skipped_count');
                    }
                }
            }

            $execution->update([
                'finished_at' => now(),
                'result' => 'success',
                'details' => [
                    'phase' => 'completed',
                    'current_team' => null,
                    'processed_teams' => $teams->count(),
                    'total_teams' => $teams->count(),
                    'message' => 'Importacio de partits completada.',
                ],
            ]);
        } catch (\Throwable $exception) {
            $execution->increment('error_count');
            $execution->update([
                'finished_at' => now(),
                'result' => 'error',
                'details' => array_merge($execution->details ?? [], [
                    'phase' => 'error',
                    'message' => $exception->getMessage(),
                ]),
            ]);
        }

        return $execution->fresh();
    }

    public function extractTeams(array $clubSheet): array
    {
        $teams = [];
        $categories = $clubSheet['categories'] ?? [];

        foreach ($categories as $category) {
            foreach (($category['teams'] ?? []) as $team) {
                $name = trim((string) ($team['name'] ?? ''));
                $fbibTeamId = (int) ($team['idSignedTeam'] ?? 0);

                if ($name === '' || $fbibTeamId === 0) {
                    continue;
                }

                $categoryName = trim((string) ($team['categoriesRegistredName'] ?? $category['name'] ?? '')) ?: null;

                $teams[] = [
                    'fbib_team_id' => $fbibTeamId,
                    'name' => $name,
                    'display_name' => $name,
                    'category' => $categoryName,
                    'sponsor' => $this->extractSponsor($name),
                    'competition_group' => $team['registredGroups'] ?? null,
                    'gender' => $team['gender'] ?: $this->guessGender($categoryName ?: $name),
                    'level' => $this->guessLevel($categoryName ?: $name),
                    'url' => "https://www.fbib.es/equipo/{$fbibTeamId}",
                    'raw' => $team,
                ];
            }
        }

        return collect($teams)->unique('fbib_team_id')->values()->all();
    }

    public function extractMatches(array $matchesData): array
    {
        $matches = [];

        foreach ($matchesData as $match) {
            $dateTime = ! empty($match['matchDay'])
                ? Carbon::parse($match['matchDay'])
                : null;

            $matchDate = $dateTime?->toDateString();
            $matchTime = $dateTime ? $this->extractMatchTime($match, $dateTime) : null;

            $matches[] = [
                'fbib_match_id' => isset($match['idMatch']) ? (int) $match['idMatch'] : null,
                'home_team' => trim((string) ($match['nameLocalTeam'] ?? '')),
                'away_team' => trim((string) ($match['nameVisitorTeam'] ?? '')),
                'competition' => $this->extractCompetition($match),
                'match_date' => $matchDate,
                'match_time' => $matchTime,
                'match_datetime' => $matchDate && $matchTime ? "{$matchDate} {$matchTime}:00" : null,
                'round' => isset($match['numMatchDay']) ? (int) $match['numMatchDay'] : null,
                'status' => $this->guessStatus($match),
                'pavilion' => trim((string) ($match['nameField'] ?? '')) ?: null,
                'score_home' => is_numeric($match['localScore'] ?? null) ? (int) $match['localScore'] : null,
                'score_away' => is_numeric($match['visitorScore'] ?? null) ? (int) $match['visitorScore'] : null,
                'import_month' => $dateTime?->month,
                'raw' => $match,
            ];
        }

        return collect($matches)
            ->filter(fn (array $match): bool => $match['home_team'] !== '' && $match['away_team'] !== '')
            ->unique(fn (array $match): string => ($match['fbib_match_id'] ?? 'null').'|'.$match['home_team'].'|'.$match['away_team'].'|'.$match['match_datetime'])
            ->values()
            ->all();
    }

    private function startExecution(string $type, ImportClub $club, ?int $executionId = null): ImportExecution
    {
        if ($executionId) {
            return ImportExecution::findOrFail($executionId);
        }

        return ImportExecution::create([
            'type' => $type,
            'import_club_id' => $club->id,
            'started_at' => now(),
            'result' => 'running',
        ]);
    }

    private function become(?User $user): void
    {
        if ($user) {
            Auth::login($user);
        }
    }

    private function resolveMatch(Team $team, array $payload): ?ClubMatch
    {
        if ($payload['fbib_match_id']) {
            return ClubMatch::query()->where('fbib_match_id', $payload['fbib_match_id'])->first();
        }

        return ClubMatch::query()
            ->where('team_id', $team->id)
            ->where('home_team', $payload['home_team'])
            ->where('away_team', $payload['away_team'])
            ->where('match_datetime', $payload['match_datetime'])
            ->first();
    }

    private function guessCategory(string $text): ?string
    {
        return str($text)->contains(['mini', 'junior', 'cadet', 'sènior', 'senior', 'infantil', 'premini', 'iniciació', 'iniciacio', 'lliga', '3x3'], true)
            ? $text
            : null;
    }

    private function extractSponsor(string $text): ?string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($text));

        if (! str_contains($normalized, 'LA SALLE PALMA')) {
            return null;
        }

        $sponsor = trim(str_replace('LA SALLE PALMA', '', $normalized));

        return $sponsor !== '' ? $sponsor : null;
    }

    private function guessGender(?string $text): ?string
    {
        $text = (string) $text;

        if (str($text)->contains(['masculí', 'masculi', 'masculino'], true)) {
            return 'M';
        }

        if (str($text)->contains(['femení', 'femeni', 'femenino'], true)) {
            return 'F';
        }

        return null;
    }

    private function guessLevel(?string $text): ?string
    {
        $text = (string) $text;

        foreach (['interilles', 'lliga balear', 'preferent', '3x3'] as $level) {
            if (str($text)->contains($level, true)) {
                return mb_strtoupper($level);
            }
        }

        return null;
    }

    private function syncImportData(Team|ClubMatch $model, array $payload): void
    {
        $current = $model->getAttribute('import_data') ?? [];

        if ($this->normalizePayload($current) !== $this->normalizePayload($payload)) {
            $model->import_data = $payload;
        }
    }

    private function normalizePayload(mixed $payload): string
    {
        if (! is_array($payload)) {
            return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null';
        }

        $normalize = function (array $value) use (&$normalize): array {
            ksort($value);

            foreach ($value as $key => $item) {
                if (is_array($item)) {
                    $value[$key] = $normalize($item);
                }
            }

            return $value;
        };

        return json_encode($normalize($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    private function updateExecutionProgress(ImportExecution $execution, array $details): void
    {
        $execution->forceFill([
            'result' => 'running',
            'details' => array_merge($execution->details ?? [], $details),
        ])->save();
    }

    private function guessStatus(array $payload): string
    {
        $state = (string) ($payload['state'] ?? '');
        $localScore = $payload['localScore'] ?? null;
        $visitorScore = $payload['visitorScore'] ?? null;
        $description = mb_strtolower(trim((string) ($payload['description'] ?? '')));

        if (str_contains($description, 'ajorn')) {
            return 'AJORNAT';
        }

        if ($this->hasScores($payload)) {
            return 'FINALITZAT';
        }

        if (in_array($state, ['4', '5'], true)) {
            return 'AJORNAT';
        }

        return 'PROGRAMAT';
    }

    private function hasScores(array $payload): bool
    {
        $localScore = $payload['localScore'] ?? null;
        $visitorScore = $payload['visitorScore'] ?? null;

        return $localScore !== null && $visitorScore !== null && $localScore !== '' && $visitorScore !== '';
    }

    private function extractMatchTime(array $payload, Carbon $dateTime): ?string
    {
        $rawTime = trim((string) ($payload['hourMatch'] ?? $payload['hour'] ?? ''));

        if ($rawTime !== '') {
            if (preg_match('/^\d{1,2}:\d{2}/', $rawTime, $matches) === 1) {
                return substr($matches[0], 0, 5);
            }

            try {
                return Carbon::parse($rawTime)->format('H:i');
            } catch (\Throwable) {
                return $dateTime->format('H:i');
            }
        }

        return $dateTime->format('H:i') !== '00:00'
            ? $dateTime->format('H:i')
            : null;
    }

    private function applyImportedMatchUpdates(ClubMatch $match, array $attributes): void
    {
        $currentImportData = is_array($match->import_data) ? $match->import_data : [];
        $previousImportedValues = $this->extractImportedValues($currentImportData);
        $updates = [
            'fbib_match_id' => $match->fbib_match_id ?? $attributes['fbib_match_id'],
            'import_month' => $attributes['import_month'],
            'competition' => $this->resolveImportedFieldValue($match, 'competition', $attributes['competition'], $previousImportedValues),
            'status' => $this->resolveImportedFieldValue($match, 'status', $attributes['status'], $previousImportedValues),
            'match_date' => $this->resolveImportedFieldValue($match, 'match_date', $attributes['match_date'], $previousImportedValues),
            'match_time' => $this->resolveImportedFieldValue($match, 'match_time', $attributes['match_time'], $previousImportedValues),
            'match_datetime' => $this->resolveImportedFieldValue($match, 'match_datetime', $attributes['match_datetime'], $previousImportedValues),
            'score_home' => $this->resolveImportedFieldValue($match, 'score_home', $attributes['score_home'], $previousImportedValues),
            'score_away' => $this->resolveImportedFieldValue($match, 'score_away', $attributes['score_away'], $previousImportedValues),
        ];

        $match->fill($updates);
        $this->syncImportData($match, $this->buildMatchImportData($attributes['import_data'], $attributes));
    }

    private function buildMatchImportData(array $payload, array $attributes): array
    {
        $payload['_imported_values'] = [
            'competition' => $attributes['competition'],
            'match_date' => $attributes['match_date'],
            'match_time' => $attributes['match_time'],
            'match_datetime' => $attributes['match_datetime'],
            'status' => $attributes['status'],
            'score_home' => $attributes['score_home'],
            'score_away' => $attributes['score_away'],
        ];

        return $payload;
    }

    private function extractCompetition(array $payload): ?string
    {
        $category = trim((string) ($payload['nameCategorySigned'] ?? $payload['nameCategory'] ?? ''));
        $competition = trim((string) ($payload['competition'] ?? $payload['competitionName'] ?? $payload['nameCompetition'] ?? ''));
        $group = trim((string) ($payload['nameGroup'] ?? $payload['groupName'] ?? $payload['groupCompetition'] ?? $payload['registredGroups'] ?? ''));

        $parts = [];

        foreach ([$category, $competition] as $part) {
            if ($part !== '' && ! in_array($part, $parts, true)) {
                $parts[] = $part;
            }
        }

        if ($group !== '' && ! in_array($group, $parts, true) && mb_strtolower($group) !== 'encreuaments') {
            $parts[] = $group;
        }

        if ($parts !== []) {
            return implode(' - ', $parts);
        }

        return null;
    }

    private function extractImportedValues(array $importData): Collection
    {
        return collect($importData['_imported_values'] ?? [])
            ->map(fn (mixed $value, string $field) => $this->normalizeComparableFieldValue($field, $value));
    }

    private function resolveImportedFieldValue(ClubMatch $match, string $field, mixed $newImportedValue, Collection $previousImportedValues): mixed
    {
        $normalizedCurrent = $this->normalizeComparableFieldValue($field, $match->getAttribute($field));

        if (! $previousImportedValues->has($field)) {
            return $normalizedCurrent === null || $normalizedCurrent === ''
                ? $newImportedValue
                : $match->getAttribute($field);
        }

        $normalizedPrevious = $previousImportedValues->get($field);

        return $normalizedCurrent === $normalizedPrevious
            ? $newImportedValue
            : $match->getAttribute($field);
    }

    private function normalizeComparableFieldValue(string $field, mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($field) {
            'match_date' => $value instanceof Carbon ? $value->toDateString() : Carbon::parse((string) $value)->toDateString(),
            'match_time' => substr((string) $value, 0, 5),
            'match_datetime' => $value instanceof Carbon ? $value->format('Y-m-d H:i:s') : Carbon::parse((string) $value)->format('Y-m-d H:i:s'),
            'score_home', 'score_away' => (int) $value,
            default => (string) $value,
        };
    }
}
