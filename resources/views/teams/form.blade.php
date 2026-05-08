@extends('layouts.app', ['title' => $team->exists ? 'Editar equip' : 'Nou equip', 'heading' => $team->exists ? 'Editar equip' : 'Nou equip'])
@section('content')
<form class="panel grid gap-5 p-6 md:grid-cols-2" method="POST" action="{{ $team->exists ? route('teams.update', $team) : route('teams.store') }}">
    @csrf @if($team->exists) @method('PUT') @endif
    <div><label class="label">Temporada</label><select class="field" name="season_id">@foreach($seasons as $season)<option value="{{ $season->id }}" @selected(old('season_id', $team->season_id)==$season->id)>{{ $season->name }}</option>@endforeach</select></div>
    <div><label class="label">Club importació</label><select class="field" name="import_club_id"><option value="">Sense club</option>@foreach($clubs as $club)<option value="{{ $club->id }}" @selected(old('import_club_id', $team->import_club_id)==$club->id)>{{ $club->name }}</option>@endforeach</select></div>
    <div><label class="label">Nom</label><input class="field" name="name" value="{{ old('name', $team->name) }}" required></div>
    <div><label class="label">Nom visible</label><input class="field" name="display_name" value="{{ old('display_name', $team->display_name) }}"></div>
    <div><label class="label">FBIB Team ID</label><input class="field" type="number" name="fbib_team_id" value="{{ old('fbib_team_id', $team->fbib_team_id) }}"></div>
    <div><label class="label">Categoria</label><input class="field" name="category" value="{{ old('category', $team->category) }}"></div>
    <div><label class="label">Patrocinador</label><input class="field" name="sponsor" value="{{ old('sponsor', $team->sponsor) }}"></div>
    <div><label class="label">Grup competició</label><input class="field" name="competition_group" value="{{ old('competition_group', $team->competition_group) }}"></div>
    <div><label class="label">Gènere</label><input class="field" name="gender" value="{{ old('gender', $team->gender) }}"></div>
    <div><label class="label">Nivell</label><input class="field" name="level" value="{{ old('level', $team->level) }}"></div>
    <div class="md:col-span-2"><label class="label">URL FBIB</label><input class="field" name="url_fbib" value="{{ old('url_fbib', $team->url_fbib) }}"></div>
    <div class="md:col-span-2"><label class="label">Notes</label><textarea class="field" name="notes">{{ old('notes', $team->notes) }}</textarea></div>
    <label class="flex items-center gap-3"><input type="checkbox" name="active" value="1" @checked(old('active', $team->active ?? true))> Actiu</label>
    <label class="flex items-center gap-3"><input type="checkbox" name="is_imported" value="1" @checked(old('is_imported', $team->is_imported))> Importat</label>
    <div class="md:col-span-2"><button class="btn-primary">Guardar</button></div>
</form>
@if($team->exists)
    <form method="POST" action="{{ route('teams.destroy', $team) }}" class="mt-4">@csrf @method('DELETE')<button class="btn-danger">Eliminar equip</button></form>
@endif
@endsection
