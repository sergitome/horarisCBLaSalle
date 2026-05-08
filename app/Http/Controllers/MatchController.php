<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Http\Requests\Matches\StoreMatchRequest;
use App\Http\Requests\Matches\UpdateMatchRequest;
use App\Models\ClubMatch;
use App\Models\LockerRoom;
use App\Models\Season;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchController extends Controller
{
    use AuthorizesCrud;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ClubMatch::class);

        $baseQuery = $this->filteredMatchesQuery($request);
        $pendingMatches = (clone $baseQuery)
            ->where(function (Builder $query): void {
                $query->whereNull('matches.score_home')
                    ->orWhereNull('matches.score_away');
            })
            ->orderByRaw('case when matches.match_date is null and matches.match_datetime is null then 1 else 0 end')
            ->orderBy('matches.match_date')
            ->orderBy('match_teams.category')
            ->orderBy('matches.match_time')
            ->get();
        $pastMatches = (clone $baseQuery)
            ->whereNotNull('matches.score_home')
            ->whereNotNull('matches.score_away')
            ->orderByDesc('matches.match_date')
            ->orderBy('match_teams.category')
            ->orderByDesc('matches.match_time')
            ->get();

        return view('matches.index', [
            'pendingMatches' => $pendingMatches,
            'pastMatches' => $pastMatches,
            'seasons' => Season::orderByDesc('start_date')->get(),
            'teams' => Team::orderBy('name')->get(),
            'lockerRooms' => LockerRoom::orderBy('name')->get(),
        ]);
    }

    public function betweenDates(Request $request): View
    {
        $this->authorize('viewAny', ClubMatch::class);

        $matches = collect();

        if ($request->filled(['start_date', 'end_date'])) {
            $validated = $request->validate([
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            ]);

            $matches = ClubMatch::query()
                ->with(['season', 'team', 'lockerRoom'])
                ->leftJoin('teams as match_teams', 'match_teams.id', '=', 'matches.team_id')
                ->select('matches.*')
                ->whereBetween('matches.match_date', [$validated['start_date'], $validated['end_date']])
                ->orderBy('matches.match_date')
                ->orderBy('match_teams.category')
                ->orderBy('matches.match_time')
                ->orderBy('match_teams.display_name')
                ->get();
        }

        return view('matches.between-dates', [
            'matches' => $matches,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ClubMatch::class);

        return view('matches.form', [
            'match' => new ClubMatch(),
            'seasons' => Season::orderByDesc('start_date')->get(),
            'teams' => Team::orderBy('name')->get(),
            'lockerRooms' => LockerRoom::orderBy('name')->get(),
        ]);
    }

    public function store(StoreMatchRequest $request): RedirectResponse
    {
        ClubMatch::create($this->normalizeMatchPayload($request->validated()) + [
            'is_imported' => $request->boolean('is_imported'),
        ]);

        return redirect()->route('matches.index')->with('status', 'Partit creat.');
    }

    public function edit(ClubMatch $match): View
    {
        $this->authorize('update', $match);

        return view('matches.form', [
            'match' => $match,
            'seasons' => Season::orderByDesc('start_date')->get(),
            'teams' => Team::orderBy('name')->get(),
            'lockerRooms' => LockerRoom::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateMatchRequest $request, ClubMatch $match): RedirectResponse
    {
        $match->update($this->normalizeMatchPayload($request->validated()) + [
            'is_imported' => $request->boolean('is_imported'),
        ]);

        return redirect()->route('matches.index')->with('status', 'Partit actualitzat.');
    }

    public function updateSchedule(Request $request, ClubMatch $match): RedirectResponse
    {
        $this->authorize('update', $match);

        $validated = $request->validate([
            'match_date' => ['required', 'date'],
            'match_time' => ['nullable', 'date_format:H:i'],
            'redirect_start_date' => ['nullable', 'date'],
            'redirect_end_date' => ['nullable', 'date'],
        ]);

        $match->update($this->normalizeMatchPayload($validated));

        return redirect()
            ->route('matches.between-dates', array_filter([
                'start_date' => $validated['redirect_start_date'] ?? null,
                'end_date' => $validated['redirect_end_date'] ?? null,
            ]))
            ->with('status', 'Dia i hora del partit actualitzats.');
    }

    public function destroy(ClubMatch $match): RedirectResponse
    {
        $this->authorize('delete', $match);
        $match->delete();

        return redirect()->route('matches.index')->with('status', 'Partit eliminat.');
    }

    private function filteredMatchesQuery(Request $request): Builder
    {
        $query = ClubMatch::query()
            ->with(['season', 'team', 'lockerRoom'])
            ->leftJoin('teams as match_teams', 'match_teams.id', '=', 'matches.team_id')
            ->select('matches.*');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(fn (Builder $subquery) => $subquery
                ->where('matches.home_team', 'like', "%{$search}%")
                ->orWhere('matches.away_team', 'like', "%{$search}%")
                ->orWhere('matches.competition', 'like', "%{$search}%")
                ->orWhere('matches.pavilion', 'like', "%{$search}%"));
        }

        foreach (['season_id', 'team_id', 'locker_room_id'] as $filter) {
            if ($value = $request->input($filter)) {
                $query->where("matches.{$filter}", $value);
            }
        }

        return $query;
    }

    private function normalizeMatchPayload(array $data): array
    {
        $date = $data['match_date'] ?? null;
        $time = isset($data['match_time']) && $data['match_time'] !== '' ? substr((string) $data['match_time'], 0, 5) : null;
        $dateTime = $data['match_datetime'] ?? null;

        if ($dateTime) {
            $parsedDateTime = Carbon::parse((string) $dateTime);
            $date ??= $parsedDateTime->toDateString();
            $time ??= $parsedDateTime->format('H:i');
        }

        $data['match_date'] = $date;
        $data['match_time'] = $time;
        $data['match_datetime'] = $date && $time ? "{$date} {$time}:00" : null;

        unset($data['redirect_start_date'], $data['redirect_end_date']);

        return $data;
    }
}
