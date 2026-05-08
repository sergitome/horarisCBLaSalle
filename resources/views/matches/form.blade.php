@extends('layouts.app', ['title' => $match->exists ? 'Editar partit' : 'Nou partit', 'heading' => $match->exists ? 'Editar partit' : 'Nou partit'])
@section('content')
<form class="panel grid gap-5 p-6 md:grid-cols-2" method="POST" action="{{ $match->exists ? route('matches.update', $match) : route('matches.store') }}">
    @csrf @if($match->exists) @method('PUT') @endif
    <div><label class="label">Temporada</label><select class="field" name="season_id">@foreach($seasons as $season)<option value="{{ $season->id }}" @selected(old('season_id', $match->season_id)==$season->id)>{{ $season->name }}</option>@endforeach</select></div>
    <div><label class="label">Equip</label><select class="field" name="team_id">@foreach($teams as $team)<option value="{{ $team->id }}" @selected(old('team_id', $match->team_id)==$team->id)>{{ $team->display_name ?: $team->name }}</option>@endforeach</select></div>
    <div><label class="label">Local</label><input class="field" name="home_team" value="{{ old('home_team', $match->home_team) }}" required></div>
    <div><label class="label">Visitant</label><input class="field" name="away_team" value="{{ old('away_team', $match->away_team) }}" required></div>
    <div class="md:col-span-2"><label class="label">Competicio</label><input class="field" name="competition" value="{{ old('competition', $match->competition) }}"></div>
    <div><label class="label">Data</label><input class="field" type="date" name="match_date" value="{{ old('match_date', optional($match->match_date)->toDateString()) }}"></div>
    <div><label class="label">Hora</label><input class="field" type="time" name="match_time" value="{{ old('match_time', $match->formatted_match_time) }}"></div>
    <div><label class="label">Data i hora</label><input class="field" type="datetime-local" name="match_datetime" value="{{ old('match_datetime', optional($match->match_datetime)->format('Y-m-d\TH:i')) }}"></div>
    <div><label class="label">Jornada</label><input class="field" name="round" value="{{ old('round', $match->round) }}"></div>
    <div><label class="label">Estat</label><input class="field" name="status" value="{{ old('status', $match->status) }}"></div>
    <div><label class="label">Pavelló</label><input class="field" name="pavilion" value="{{ old('pavilion', $match->pavilion) }}"></div>
    <div><label class="label">Punts local</label><input class="field" type="number" name="score_home" value="{{ old('score_home', $match->score_home) }}"></div>
    <div><label class="label">Punts visitant</label><input class="field" type="number" name="score_away" value="{{ old('score_away', $match->score_away) }}"></div>
    <div><label class="label">Vestidor</label><select class="field" name="locker_room_id"><option value="">Sense assignar</option>@foreach($lockerRooms as $lockerRoom)<option value="{{ $lockerRoom->id }}" @selected(old('locker_room_id', $match->locker_room_id)==$lockerRoom->id)>{{ $lockerRoom->name }}</option>@endforeach</select></div>
    <div><label class="label">FBIB Match ID</label><input class="field" type="number" name="fbib_match_id" value="{{ old('fbib_match_id', $match->fbib_match_id) }}"></div>
    <div class="md:col-span-2"><label class="label">Notes</label><textarea class="field" name="notes">{{ old('notes', $match->notes) }}</textarea></div>
    <label class="flex items-center gap-3 md:col-span-2"><input type="checkbox" name="is_imported" value="1" @checked(old('is_imported', $match->is_imported))> Importat</label>
    <div class="md:col-span-2"><button class="btn-primary">Guardar</button></div>
</form>
@if($match->exists)
    <form method="POST" action="{{ route('matches.destroy', $match) }}" class="mt-4">@csrf @method('DELETE')<button class="btn-danger">Eliminar partit</button></form>
@endif
@endsection
