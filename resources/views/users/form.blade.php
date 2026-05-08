@extends('layouts.app', ['title' => $userModel->exists ? 'Editar usuari' : 'Nou usuari', 'heading' => $userModel->exists ? 'Editar usuari' : 'Nou usuari'])

@section('content')
<form class="panel grid gap-5 p-6 md:grid-cols-2" method="POST" action="{{ $userModel->exists ? route('users.update', $userModel) : route('users.store') }}">
    @csrf
    @if($userModel->exists) @method('PUT') @endif
    <div><label class="label">Username</label><input class="field" name="username" value="{{ old('username', $userModel->username) }}" required></div>
    <div><label class="label">Email</label><input class="field" type="email" name="email" value="{{ old('email', $userModel->email) }}" required></div>
    <div><label class="label">Nom</label><input class="field" name="name" value="{{ old('name', $userModel->name) }}" required></div>
    <div><label class="label">Cognoms</label><input class="field" name="surname" value="{{ old('surname', $userModel->surname) }}" required></div>
    <div><label class="label">Contrasenya {{ $userModel->exists ? '(opcional)' : '' }}</label><input class="field" type="password" name="password" {{ $userModel->exists ? '' : 'required' }}></div>
    <div><label class="label">Rol</label><select class="field" name="role">@foreach($roles as $role)<option value="{{ $role->value }}" @selected(old('role', $userModel->role?->value) === $role->value)>{{ $role->value }}</option>@endforeach</select></div>
    <label class="flex items-center gap-3 md:col-span-2"><input type="checkbox" name="active" value="1" @checked(old('active', $userModel->active ?? true))> Actiu</label>
    <div class="md:col-span-2 flex gap-3">
        <button class="btn-primary" type="submit">Guardar</button>
    </div>
</form>
@if($userModel->exists)
    <form method="POST" action="{{ route('users.destroy', $userModel) }}" class="mt-4">
        @csrf @method('DELETE')
        <button class="btn-danger" type="submit">Eliminar usuari</button>
    </form>
@endif
@endsection
