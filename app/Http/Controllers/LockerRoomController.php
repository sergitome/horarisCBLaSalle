<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Http\Requests\LockerRooms\StoreLockerRoomRequest;
use App\Http\Requests\LockerRooms\UpdateLockerRoomRequest;
use App\Models\LockerRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LockerRoomController extends Controller
{
    use AuthorizesCrud;

    public function index(): View
    {
        $this->authorize('viewAny', LockerRoom::class);

        $query = LockerRoom::query();

        if ($search = request('search')) {
            $query->where(fn ($subquery) => $subquery
                ->where('name', 'like', "%{$search}%")
                ->orWhere('pavilion', 'like', "%{$search}%"));
        }

        $this->booleanFilter($query, 'active', 'active');

        return view('locker-rooms.index', [
            'lockerRooms' => $query->orderBy('name')->paginate()->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', LockerRoom::class);

        return view('locker-rooms.form', ['lockerRoom' => new LockerRoom()]);
    }

    public function store(StoreLockerRoomRequest $request): RedirectResponse
    {
        LockerRoom::create($request->validated() + ['active' => $request->boolean('active')]);

        return redirect()->route('locker-rooms.index')->with('status', 'Vestidor creat.');
    }

    public function edit(LockerRoom $locker_room): View
    {
        $this->authorize('update', $locker_room);

        return view('locker-rooms.form', ['lockerRoom' => $locker_room]);
    }

    public function update(UpdateLockerRoomRequest $request, LockerRoom $locker_room): RedirectResponse
    {
        $locker_room->update($request->validated() + ['active' => $request->boolean('active')]);

        return redirect()->route('locker-rooms.index')->with('status', 'Vestidor actualitzat.');
    }

    public function destroy(LockerRoom $locker_room): RedirectResponse
    {
        $this->authorize('delete', $locker_room);
        $locker_room->delete();

        return redirect()->route('locker-rooms.index')->with('status', 'Vestidor eliminat.');
    }
}
