<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Http\Requests\Teams\StoreTeamRequest;
use App\Http\Requests\Teams\UpdateTeamRequest;
use App\Models\ImportClub;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeamController extends Controller
{
    use AuthorizesCrud;

    public function index(): View
    {
        $this->authorize('viewAny', Team::class);

        $query = Team::with(['season', 'importClub']);
        $currentSeason = Season::query()->where('active', true)->first() ?? Season::query()->latest('start_date')->first();

        if ($search = request('search')) {
            $query->where(fn ($subquery) => $subquery
                ->where('name', 'like', "%{$search}%")
                ->orWhere('display_name', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%"));
        }

        if ($seasonId = request('season_id')) {
            $query->where('season_id', $seasonId);
        }

        if ($clubId = request('import_club_id')) {
            $query->where('import_club_id', $clubId);
        }

        $this->booleanFilter($query, 'active', 'active');

        return view('teams.index', [
            'teams' => $query->orderBy('name')->paginate()->withQueryString(),
            'seasons' => Season::orderByDesc('start_date')->get(),
            'clubs' => ImportClub::orderBy('name')->get(),
            'currentSeason' => $currentSeason,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Team::class);

        return view('teams.form', [
            'team' => new Team(),
            'seasons' => Season::orderByDesc('start_date')->get(),
            'clubs' => ImportClub::orderBy('name')->get(),
        ]);
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        Team::create($request->validated() + [
            'active' => $request->boolean('active'),
            'is_imported' => $request->boolean('is_imported'),
        ]);

        return redirect()->route('teams.index')->with('status', 'Equip creat.');
    }

    public function edit(Team $team): View
    {
        $this->authorize('update', $team);

        return view('teams.form', [
            'team' => $team,
            'seasons' => Season::orderByDesc('start_date')->get(),
            'clubs' => ImportClub::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $team->update($request->validated() + [
            'active' => $request->boolean('active'),
            'is_imported' => $request->boolean('is_imported'),
        ]);

        return redirect()->route('teams.index')->with('status', 'Equip actualitzat.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $this->authorize('delete', $team);
        $team->delete();

        return redirect()->route('teams.index')->with('status', 'Equip eliminat.');
    }
}
