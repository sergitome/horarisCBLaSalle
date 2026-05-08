@extends('layouts.app', ['title' => 'Dashboard', 'heading' => 'Dashboard'])

@section('content')
<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @foreach([
        ['label' => 'Usuaris', 'value' => $stats['users'], 'icon' => 'group'],
        ['label' => 'Temporades', 'value' => $stats['seasons'], 'icon' => 'event'],
        ['label' => 'Clubs', 'value' => $stats['clubs'], 'icon' => 'apartment'],
        ['label' => 'Equips', 'value' => $stats['teams'], 'icon' => 'sports_basketball'],
        ['label' => 'Partits', 'value' => $stats['matches'], 'icon' => 'calendar_month'],
        ['label' => 'Vestidors', 'value' => $stats['lockerRooms'], 'icon' => 'meeting_room'],
    ] as $card)
        <article class="panel p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-stone-500">{{ $card['label'] }}</p>
                    <p class="mt-2 text-4xl font-bold">{{ $card['value'] }}</p>
                </div>
                <span class="material-icons-outlined rounded-2xl bg-amber-100 p-4 text-3xl text-amber-700">{{ $card['icon'] }}</span>
            </div>
        </article>
    @endforeach
</section>

<section class="mt-6 grid gap-6 xl:grid-cols-2">
    <article class="panel p-6">
        <h3 class="text-xl font-bold">Propers partits</h3>
        <div class="mt-4 space-y-3">
            @forelse($upcomingMatches as $match)
                <div class="rounded-2xl bg-stone-50 p-4">
                    <p class="font-semibold">{{ $match->home_team }} vs {{ $match->away_team }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ $match->competition ?: 'Sense competicio' }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ optional($match->match_datetime)->format('d/m/Y H:i') }} · {{ $match->pavilion ?: 'Sense pavelló' }}</p>
                </div>
            @empty
                <p class="text-sm text-stone-500">Encara no hi ha partits programats.</p>
            @endforelse
        </div>
    </article>
    <article class="panel p-6">
        <h3 class="text-xl font-bold">Últimes importacions</h3>
        <div class="mt-4 space-y-3">
            @forelse($latestImports as $execution)
                <div class="rounded-2xl bg-stone-50 p-4">
                    <p class="font-semibold">{{ strtoupper($execution->type) }} · {{ $execution->importClub?->name ?? 'Sense club' }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ optional($execution->started_at)->format('d/m/Y H:i') }} · resultat {{ $execution->result }}</p>
                </div>
            @empty
                <p class="text-sm text-stone-500">Sense historial d’importacions.</p>
            @endforelse
        </div>
    </article>
</section>

<section class="panel mt-6 p-6">
    <h3 class="text-xl font-bold">Activitat recent</h3>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="text-stone-500">
            <tr>
                <th class="pb-3">Usuari</th>
                <th class="pb-3">Acció</th>
                <th class="pb-3">Entitat</th>
                <th class="pb-3">Moment</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
            @forelse($latestLogs as $log)
                <tr>
                    <td class="py-3">{{ $log->username ?: 'Sistema' }}</td>
                    <td class="py-3">{{ $log->action }}</td>
                    <td class="py-3">{{ $log->entity }}</td>
                    <td class="py-3">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-4 text-stone-500">Sense moviments registrats.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
