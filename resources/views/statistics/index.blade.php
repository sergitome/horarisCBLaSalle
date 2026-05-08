@extends('layouts.app', ['title' => 'Estadistiques', 'heading' => 'Estadistiques de temporada'])
@section('content')
<div class="space-y-6">
    <div class="panel p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <form class="grid gap-3 md:grid-cols-[minmax(0,18rem)_minmax(0,20rem)_auto]">
                <select class="field" name="season_id">
                    <option value="">Selecciona temporada</option>
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}" @selected((string) request('season_id', $selectedSeason?->id) === (string) $season->id)>{{ $season->name }}</option>
                    @endforeach
                </select>
                <select class="field" name="team_id">
                    <option value="">Tots els equips</option>
                    @foreach($seasonTeams as $team)
                        <option value="{{ $team->id }}" @selected((string) request('team_id', $selectedTeam?->id) === (string) $team->id)>{{ $team->display_name ?: $team->name }}</option>
                    @endforeach
                </select>
                <button class="btn-secondary">Veure estadistiques</button>
            </form>
            <div class="text-sm text-stone-500">
                @if($selectedSeason)
                    Resum de la temporada <span class="font-semibold text-stone-900">{{ $selectedSeason->name }}</span>
                    @if($selectedTeam)
                        del equip <span class="font-semibold text-stone-900">{{ $selectedTeam->display_name ?: $selectedTeam->name }}</span>
                    @endif
                @else
                    No hi ha temporades disponibles.
                @endif
            </div>
        </div>
    </div>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Temporada', 'value' => $summary['selected_season_name'] ?: '-', 'icon' => 'event'],
            ['label' => 'Equip', 'value' => $summary['selected_team_name'] ?: 'Tots', 'icon' => 'sports_basketball'],
            ['label' => 'Equips amb partits', 'value' => $summary['teams_count'], 'icon' => 'groups'],
            ['label' => 'Competicions', 'value' => $summary['competitions_count'], 'icon' => 'emoji_events'],
            ['label' => 'Partits jugats', 'value' => $summary['played_matches'], 'icon' => 'scoreboard'],
            ['label' => 'Partits pendents', 'value' => $summary['pending_matches'], 'icon' => 'hourglass_top'],
        ] as $card)
            <article class="panel p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-stone-500">{{ $card['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold text-stone-900">{{ $card['value'] }}</p>
                    </div>
                    <span class="material-icons-outlined rounded-2xl bg-amber-100 p-4 text-3xl text-amber-700">{{ $card['icon'] }}</span>
                </div>
            </article>
        @endforeach
    </section>

    <div class="panel p-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-stone-900">Per competicio</h3>
                <p class="text-sm text-stone-500">Balance, partits pendents i mitjanes de punts agregades per competicio.</p>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-stone-700">{{ $summary['competitions_count'] }} competicions</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="text-stone-500">
                        <th class="pb-3">Competicio</th>
                        <th class="pb-3">Equips</th>
                        <th class="pb-3">Pendents</th>
                        <th class="pb-3">Local</th>
                        <th class="pb-3">Visitant</th>
                        <th class="pb-3">Total</th>
                        <th class="pb-3">PF/PC total</th>
                        <th class="pb-3">PF/PC local</th>
                        <th class="pb-3">PF/PC visitant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($competitionStats as $competition)
                        <tr>
                            <td class="py-3 font-semibold text-stone-900">{{ $competition['competition_name'] }}</td>
                            <td class="py-3">{{ $competition['teams_count'] }}</td>
                            <td class="py-3">{{ $competition['stats']['pending'] }}</td>
                            <td class="py-3">{{ $competition['stats']['home_wins'] }}G / {{ $competition['stats']['home_losses'] }}P</td>
                            <td class="py-3">{{ $competition['stats']['away_wins'] }}G / {{ $competition['stats']['away_losses'] }}P</td>
                            <td class="py-3">{{ $competition['stats']['total_wins'] }}G / {{ $competition['stats']['total_losses'] }}P</td>
                            <td class="py-3">{{ $competition['stats']['avg_points_for'] }} / {{ $competition['stats']['avg_points_against'] }}</td>
                            <td class="py-3">{{ $competition['stats']['home_avg_points_for'] }} / {{ $competition['stats']['home_avg_points_against'] }}</td>
                            <td class="py-3">{{ $competition['stats']['away_avg_points_for'] }} / {{ $competition['stats']['away_avg_points_against'] }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-4 text-stone-500" colspan="9">No hi ha competicions per aquesta temporada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(! $selectedTeam)
        <div class="panel p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-stone-900">Per club</h3>
                    <p class="text-sm text-stone-500">Balance, partits pendents i mitjanes de punts agregades de tots els equips del club.</p>
                </div>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-stone-700">{{ $summary['clubs_count'] }} clubs</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="text-stone-500">
                            <th class="pb-3">Club</th>
                            <th class="pb-3">Equips</th>
                            <th class="pb-3">Pendents</th>
                            <th class="pb-3">Local</th>
                            <th class="pb-3">Visitant</th>
                            <th class="pb-3">Total</th>
                            <th class="pb-3">PF/PC total</th>
                            <th class="pb-3">PF/PC local</th>
                            <th class="pb-3">PF/PC visitant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @forelse($clubStats as $club)
                            <tr>
                                <td class="py-3 font-semibold text-stone-900">{{ $club['club_name'] }}</td>
                                <td class="py-3">{{ $club['teams_count'] }}</td>
                                <td class="py-3">{{ $club['stats']['pending'] }}</td>
                                <td class="py-3">{{ $club['stats']['home_wins'] }}G / {{ $club['stats']['home_losses'] }}P</td>
                                <td class="py-3">{{ $club['stats']['away_wins'] }}G / {{ $club['stats']['away_losses'] }}P</td>
                                <td class="py-3">{{ $club['stats']['total_wins'] }}G / {{ $club['stats']['total_losses'] }}P</td>
                                <td class="py-3">{{ $club['stats']['avg_points_for'] }} / {{ $club['stats']['avg_points_against'] }}</td>
                                <td class="py-3">{{ $club['stats']['home_avg_points_for'] }} / {{ $club['stats']['home_avg_points_against'] }}</td>
                                <td class="py-3">{{ $club['stats']['away_avg_points_for'] }} / {{ $club['stats']['away_avg_points_against'] }}</td>
                            </tr>
                        @empty
                            <tr><td class="py-4 text-stone-500" colspan="9">No hi ha partits per aquesta temporada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="panel p-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-stone-900">Per equip</h3>
                <p class="text-sm text-stone-500">Desglossat entre local, visitant, total, partits pendents i mitjanes de punts.</p>
            </div>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">{{ $summary['teams_count'] }} equips</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="text-stone-500">
                        <th class="pb-3">Equip</th>
                        <th class="pb-3">Club</th>
                        <th class="pb-3">Categoria</th>
                        <th class="pb-3">Pendents</th>
                        <th class="pb-3">Local</th>
                        <th class="pb-3">Visitant</th>
                        <th class="pb-3">Total</th>
                        <th class="pb-3">PF/PC total</th>
                        <th class="pb-3">PF/PC local</th>
                        <th class="pb-3">PF/PC visitant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($teamStats as $team)
                        <tr>
                            <td class="py-3 font-semibold text-stone-900">{{ $team['team_name'] }}</td>
                            <td class="py-3">{{ $team['club_name'] }}</td>
                            <td class="py-3">{{ $team['category'] ?: '-' }}</td>
                            <td class="py-3">{{ $team['stats']['pending'] }}</td>
                            <td class="py-3">{{ $team['stats']['home_wins'] }}G / {{ $team['stats']['home_losses'] }}P</td>
                            <td class="py-3">{{ $team['stats']['away_wins'] }}G / {{ $team['stats']['away_losses'] }}P</td>
                            <td class="py-3">{{ $team['stats']['total_wins'] }}G / {{ $team['stats']['total_losses'] }}P</td>
                            <td class="py-3">{{ $team['stats']['avg_points_for'] }} / {{ $team['stats']['avg_points_against'] }}</td>
                            <td class="py-3">{{ $team['stats']['home_avg_points_for'] }} / {{ $team['stats']['home_avg_points_against'] }}</td>
                            <td class="py-3">{{ $team['stats']['away_avg_points_for'] }} / {{ $team['stats']['away_avg_points_against'] }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-4 text-stone-500" colspan="10">No hi ha equips amb partits en aquesta temporada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
