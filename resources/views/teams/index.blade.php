@extends('layouts.app', ['title' => 'Equips', 'heading' => 'Equips'])
@section('content')
<div class="panel p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <form class="grid gap-3 md:grid-cols-5">
            <input class="field" name="search" placeholder="Nom equip" value="{{ request('search') }}">
            <select class="field" name="season_id"><option value="">Totes les temporades</option>@foreach($seasons as $season)<option value="{{ $season->id }}" @selected(request('season_id')==$season->id)>{{ $season->name }}</option>@endforeach</select>
            <select class="field" name="import_club_id"><option value="">Tots els clubs</option>@foreach($clubs as $club)<option value="{{ $club->id }}" @selected(request('import_club_id')==$club->id)>{{ $club->name }}</option>@endforeach</select>
            <select class="field" name="active"><option value="">Tots</option><option value="1" @selected(request('active')==='1')>Actius</option><option value="0" @selected(request('active')==='0')>Inactius</option></select>
            <button class="btn-secondary">Filtrar</button>
        </form>
        <a class="btn-primary" href="{{ route('teams.create') }}">Nou equip</a>
    </div>
    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead><tr class="text-stone-500"><th class="pb-3">Equip</th><th class="pb-3">Temporada</th><th class="pb-3">Club</th><th class="pb-3">Categoria</th><th></th></tr></thead>
            <tbody class="divide-y divide-stone-200">@foreach($teams as $team)<tr><td class="py-3">{{ $team->display_name ?: $team->name }}</td><td class="py-3">{{ $team->season->name }}</td><td class="py-3">{{ $team->importClub?->name }}</td><td class="py-3">{{ $team->category }}</td><td class="py-3 text-right"><div class="flex justify-end gap-2">@if($currentSeason && $team->season_id === $currentSeason->id)<a class="btn-secondary" href="{{ route('matches.index', ['team_id' => $team->id, 'season_id' => $currentSeason->id]) }}">Veure partits</a><a class="btn-secondary" href="{{ route('statistics.index', ['team_id' => $team->id, 'season_id' => $currentSeason->id]) }}">Veure estadistiques</a>@endif<a class="btn-secondary" href="{{ route('teams.edit', $team) }}">Editar</a></div></td></tr>@endforeach</tbody>
        </table>
    </div>
    <div class="mt-4">{{ $teams->links() }}</div>
</div>
@endsection
