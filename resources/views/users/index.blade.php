@extends('layouts.app', ['title' => 'Usuaris', 'heading' => 'Usuaris'])

@section('content')
<div class="panel p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <form class="grid gap-3 md:grid-cols-4">
            <input class="field" name="search" placeholder="Cercar..." value="{{ request('search') }}">
            <select class="field" name="role">
                <option value="">Tots els rols</option>
                @foreach($roles as $role)
                    <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->value }}</option>
                @endforeach
            </select>
            <select class="field" name="active">
                <option value="">Tots</option>
                <option value="1" @selected(request('active') === '1')>Actius</option>
                <option value="0" @selected(request('active') === '0')>Inactius</option>
            </select>
            <button class="btn-secondary" type="submit">Filtrar</button>
        </form>
        @can('create', \App\Models\User::class)
            <a class="btn-primary" href="{{ route('users.create') }}">Nou usuari</a>
        @endcan
    </div>
    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead><tr class="text-stone-500"><th class="pb-3">Usuari</th><th class="pb-3">Nom</th><th class="pb-3">Rol</th><th class="pb-3">Actiu</th><th></th></tr></thead>
            <tbody class="divide-y divide-stone-200">
            @foreach($users as $user)
                <tr>
                    <td class="py-3">{{ $user->username }}</td>
                    <td class="py-3">{{ $user->full_name }}<div class="text-xs text-stone-500">{{ $user->email }}</div></td>
                    <td class="py-3">{{ $user->role->value }}</td>
                    <td class="py-3">{{ $user->active ? 'Sí' : 'No' }}</td>
                    <td class="py-3 text-right">
                        <a class="btn-secondary" href="{{ route('users.edit', $user) }}">Editar</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
