@extends('layouts.app', ['title' => 'Partits', 'heading' => 'Partits'])
@section('content')
<div class="space-y-6">
    <div class="panel p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <form class="grid gap-3 md:grid-cols-5">
                <input class="field" name="search" placeholder="Local / visitant / competicio" value="{{ request('search') }}">
                <select class="field" name="season_id"><option value="">Temporades</option>@foreach($seasons as $season)<option value="{{ $season->id }}" @selected(request('season_id')==$season->id)>{{ $season->name }}</option>@endforeach</select>
                <select class="field" name="team_id"><option value="">Equips</option>@foreach($teams as $team)<option value="{{ $team->id }}" @selected(request('team_id')==$team->id)>{{ $team->display_name ?: $team->name }}</option>@endforeach</select>
                <select class="field" name="locker_room_id"><option value="">Vestidors</option>@foreach($lockerRooms as $lockerRoom)<option value="{{ $lockerRoom->id }}" @selected(request('locker_room_id')==$lockerRoom->id)>{{ $lockerRoom->name }}</option>@endforeach</select>
                <button class="btn-secondary">Filtrar</button>
            </form>
            <div class="flex gap-3">
                <a class="btn-secondary" href="{{ route('matches.between-dates') }}">Veure per dates</a>
                <a class="btn-primary" href="{{ route('matches.create') }}">Nou partit</a>
            </div>
        </div>
    </div>

    <div class="panel p-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-stone-900">Partits no jugats</h3>
                <p class="text-sm text-stone-500">Mostra els partits pendents, amb l'horari quan estigui definit.</p>
            </div>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">{{ $pendingMatches->count() }} partits</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="text-stone-500">
                        <th class="pb-3">Dia</th>
                        <th class="pb-3">Hora</th>
                        <th class="pb-3">Categoria</th>
                        <th class="pb-3">Competicio</th>
                        <th class="pb-3">Partit</th>
                        <th class="pb-3">Equip</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($pendingMatches as $match)
                        <tr>
                            <td class="py-3">{{ optional($match->match_date)->format('d/m/Y') ?: '-' }}</td>
                            <td class="py-3">{{ $match->formatted_match_time ?: '--:--' }}</td>
                            <td class="py-3">{{ $match->team->category ?: '-' }}</td>
                            <td class="py-3">{{ $match->competition ?: '-' }}</td>
                            <td class="py-3">{{ $match->home_team }} vs {{ $match->away_team }}</td>
                            <td class="py-3">{{ $match->team->display_name ?: $match->team->name }}</td>
                            <td class="py-3 text-right"><a class="btn-secondary" href="{{ route('matches.edit', $match) }}">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td class="py-4 text-stone-500" colspan="7">No hi ha partits pendents amb aquests filtres.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel p-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-stone-900">Partits passats</h3>
                <p class="text-sm text-stone-500">Resultats ordenats per data descendent.</p>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-stone-700">{{ $pastMatches->count() }} partits</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="text-stone-500">
                        <th class="pb-3">Dia</th>
                        <th class="pb-3">Hora</th>
                        <th class="pb-3">Categoria</th>
                        <th class="pb-3">Competicio</th>
                        <th class="pb-3">Partit</th>
                        <th class="pb-3">Equip</th>
                        <th class="pb-3">Resultat</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($pastMatches as $match)
                        <tr>
                            <td class="py-3">{{ optional($match->match_date)->format('d/m/Y') ?: '-' }}</td>
                            <td class="py-3">{{ $match->formatted_match_time ?: '--:--' }}</td>
                            <td class="py-3">{{ $match->team->category ?: '-' }}</td>
                            <td class="py-3">{{ $match->competition ?: '-' }}</td>
                            <td class="py-3">{{ $match->home_team }} vs {{ $match->away_team }}</td>
                            <td class="py-3">{{ $match->team->display_name ?: $match->team->name }}</td>
                            <td class="py-3">{{ $match->score_home }} - {{ $match->score_away }}</td>
                            <td class="py-3 text-right"><a class="btn-secondary" href="{{ route('matches.edit', $match) }}">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td class="py-4 text-stone-500" colspan="8">No hi ha partits passats amb aquests filtres.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
