@extends('layouts.app', ['title' => 'Partits per dates', 'heading' => 'Partits per dates'])
@section('content')
<div class="space-y-6">
    <div class="panel p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <form class="grid gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-700" for="start_date">Data inicial</label>
                    <input class="field" id="start_date" name="start_date" type="date" value="{{ request('start_date') }}">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-700" for="end_date">Data final</label>
                    <input class="field" id="end_date" name="end_date" type="date" value="{{ request('end_date') }}">
                </div>
                <div class="flex items-end gap-3">
                    <button class="btn-primary">Cercar</button>
                    <a class="btn-secondary" href="{{ route('matches.between-dates') }}">Netejar</a>
                </div>
            </form>
            <a class="btn-secondary" href="{{ route('matches.index') }}">Tornar a partits</a>
        </div>
    </div>

    <div class="panel p-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-stone-900">Partits entre dates</h3>
                <p class="text-sm text-stone-500">Les dues dates s'inclouen. Ordenacio per dia, categoria i hora.</p>
            </div>
            @if(request()->filled(['start_date', 'end_date']))
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">{{ $matches->count() }} partits</span>
            @endif
        </div>

        @if(! request()->filled(['start_date', 'end_date']))
            <p class="text-sm text-stone-500">Selecciona una data inicial i una data final per veure els partits programats.</p>
        @elseif($matches->isEmpty())
            <p class="text-sm text-stone-500">No hi ha partits dins aquest interval.</p>
        @else
            <div class="space-y-6">
                @foreach($matches->groupBy(fn ($match) => optional($match->match_date)->format('Y-m-d') ?: 'sense-data') as $day => $dayMatches)
                    <section class="rounded-3xl border border-stone-200 p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h4 class="text-base font-bold text-stone-900">{{ $day !== 'sense-data' ? \Carbon\Carbon::parse($day)->format('d/m/Y') : 'Sense data' }}</h4>
                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">{{ $dayMatches->count() }} partits</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto border-separate border-spacing-x-6 border-spacing-y-3 text-left text-sm">
                                <thead>
                                    <tr class="text-stone-500">
                                        <th class="pb-3 whitespace-nowrap">Categoria</th>
                                        <th class="pb-3 whitespace-nowrap">Competicio</th>
                                        <th class="pb-3 whitespace-nowrap">Equip</th>
                                        <th class="pb-3 whitespace-nowrap">Partit</th>
                                        <th class="pb-3 whitespace-nowrap">Pavello</th>
                                        <th class="pb-3 whitespace-nowrap">Canviar dia i hora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dayMatches as $match)
                                        <tr>
                                            <td class="min-w-28 py-3 align-top">{{ $match->team->category ?: '-' }}</td>
                                            <td class="min-w-40 py-3 align-top break-words">{{ $match->competition ?: '-' }}</td>
                                            <td class="min-w-44 py-3 align-top break-words">{{ $match->team->display_name ?: $match->team->name }}</td>
                                            <td class="min-w-96 py-3 align-top break-words">{{ $match->home_team }} vs {{ $match->away_team }}</td>
                                            <td class="min-w-80 py-3 align-top break-words">{{ $match->pavilion ?: '-' }}</td>
                                            <td class="min-w-64 py-3 align-top">
                                                <form class="grid gap-3 md:grid-cols-1 lg:grid-cols-[1fr_1fr_auto]" method="POST" action="{{ route('matches.update-schedule', $match) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="redirect_start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="redirect_end_date" value="{{ request('end_date') }}">
                                                    <input class="field" type="date" name="match_date" value="{{ optional($match->match_date)->toDateString() }}" required>
                                                    <input class="field" type="time" name="match_time" value="{{ $match->formatted_match_time }}">
                                                    <button class="btn-secondary" type="submit">Guardar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
