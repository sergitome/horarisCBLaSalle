<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Http\Requests\ImportClubs\StoreImportClubRequest;
use App\Http\Requests\ImportClubs\UpdateImportClubRequest;
use App\Models\ImportClub;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ImportClubController extends Controller
{
    use AuthorizesCrud;

    public function index(): View
    {
        $this->authorize('viewAny', ImportClub::class);

        $query = ImportClub::query();

        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('fbib_club_id', 'like', "%{$search}%");
        }

        $this->booleanFilter($query, 'active', 'active');

        return view('import-clubs.index', [
            'clubs' => $query->orderBy('name')->paginate()->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ImportClub::class);

        return view('import-clubs.form', ['club' => new ImportClub()]);
    }

    public function store(StoreImportClubRequest $request): RedirectResponse
    {
        ImportClub::create($request->validated() + ['active' => $request->boolean('active')]);

        return redirect()->route('clubs.index')->with('status', 'Club creat.');
    }

    public function edit(ImportClub $import_club): View
    {
        $this->authorize('update', $import_club);

        return view('import-clubs.form', ['club' => $import_club]);
    }

    public function update(UpdateImportClubRequest $request, ImportClub $import_club): RedirectResponse
    {
        $import_club->update($request->validated() + ['active' => $request->boolean('active')]);

        return redirect()->route('clubs.index')->with('status', 'Club actualitzat.');
    }

    public function destroy(ImportClub $import_club): RedirectResponse
    {
        $this->authorize('delete', $import_club);
        $import_club->delete();

        return redirect()->route('clubs.index')->with('status', 'Club eliminat.');
    }
}
