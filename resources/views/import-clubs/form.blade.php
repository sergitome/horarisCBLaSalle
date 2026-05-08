@extends('layouts.app', ['title' => $club->exists ? 'Editar club' : 'Nou club', 'heading' => $club->exists ? 'Editar club' : 'Nou club'])
@section('content')
<form class="panel grid gap-5 p-6 md:grid-cols-2" method="POST" action="{{ $club->exists ? route('clubs.update', $club) : route('clubs.store') }}">
    @csrf @if($club->exists) @method('PUT') @endif
    <div><label class="label">Nom</label><input class="field" name="name" value="{{ old('name', $club->name) }}" required></div>
    <div><label class="label">FBIB Club ID</label><input class="field" name="fbib_club_id" type="number" value="{{ old('fbib_club_id', $club->fbib_club_id) }}" required></div>
    <div class="md:col-span-2"><label class="label">URL</label><input class="field" name="url" value="{{ old('url', $club->url) }}"></div>
    <label class="flex items-center gap-3 md:col-span-2"><input type="checkbox" name="active" value="1" @checked(old('active', $club->active ?? true))> Actiu</label>
    <div class="md:col-span-2"><button class="btn-primary">Guardar</button></div>
</form>
@if($club->exists)
    <form method="POST" action="{{ route('clubs.destroy', $club) }}" class="mt-4">@csrf @method('DELETE')<button class="btn-danger">Eliminar club</button></form>
@endif
@endsection
