<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Http\Requests\Seasons\StoreSeasonRequest;
use App\Http\Requests\Seasons\UpdateSeasonRequest;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeasonController extends Controller
{
    use AuthorizesCrud;

    public function index(): View
    {
        $this->authorize('viewAny', Season::class);

        $query = Season::query();

        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $this->booleanFilter($query, 'active', 'active');

        return view('seasons.index', [
            'seasons' => $query->orderByDesc('start_date')->paginate()->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Season::class);

        return view('seasons.form', ['season' => new Season()]);
    }

    public function store(StoreSeasonRequest $request): RedirectResponse
    {
        Season::create($request->validated() + ['active' => $request->boolean('active')]);

        return redirect()->route('seasons.index')->with('status', 'Temporada creada.');
    }

    public function edit(Season $season): View
    {
        $this->authorize('update', $season);

        return view('seasons.form', compact('season'));
    }

    public function update(UpdateSeasonRequest $request, Season $season): RedirectResponse
    {
        $season->update($request->validated() + ['active' => $request->boolean('active')]);

        return redirect()->route('seasons.index')->with('status', 'Temporada actualitzada.');
    }

    public function destroy(Season $season): RedirectResponse
    {
        $this->authorize('delete', $season);
        $season->delete();

        return redirect()->route('seasons.index')->with('status', 'Temporada eliminada.');
    }
}
