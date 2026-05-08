<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    use AuthorizesCrud;

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        if ($search = request('search')) {
            $query->where(function ($subquery) use ($search): void {
                $subquery
                    ->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = request('role')) {
            $query->where('role', $role);
        }

        $this->booleanFilter($query, 'active', 'active');

        return view('users.index', [
            'users' => $query->orderBy('name')->paginate()->withQueryString(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.form', [
            'userModel' => new User(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated() + ['active' => $request->boolean('active')]);

        return redirect()->route('users.index')->with('status', 'Usuari creat correctament.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.form', [
            'userModel' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data + ['active' => $request->boolean('active')]);

        return redirect()->route('users.index')->with('status', 'Usuari actualitzat.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        $user->delete();

        return redirect()->route('users.index')->with('status', 'Usuari eliminat.');
    }
}
