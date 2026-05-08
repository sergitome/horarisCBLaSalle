<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Gestio Partits' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="shell">
@php($items = [
    ['route' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
    ['route' => 'users.index', 'icon' => 'group', 'label' => 'Usuaris'],
    ['route' => 'seasons.index', 'icon' => 'event', 'label' => 'Temporades'],
    ['route' => 'clubs.index', 'icon' => 'apartment', 'label' => 'Clubs'],
    ['route' => 'teams.index', 'icon' => 'sports_basketball', 'label' => 'Equips'],
    ['route' => 'matches.index', 'icon' => 'calendar_month', 'label' => 'Partits'],
    ['route' => 'statistics.index', 'icon' => 'query_stats', 'label' => 'Estadistiques'],
    ['route' => 'matches.between-dates', 'icon' => 'date_range', 'label' => 'Partits per dates'],
    ['route' => 'locker-rooms.index', 'icon' => 'meeting_room', 'label' => 'Vestidors'],
    ['route' => 'imports.index', 'icon' => 'cloud_download', 'label' => 'Importar'],
    ['route' => 'logs.index', 'icon' => 'receipt_long', 'label' => 'Logs'],
])
@php($currentItem = collect($items)->first(fn ($item) => request()->routeIs($item['route'])))

<div class="min-h-screen">
    <main class="relative mx-auto max-w-7xl px-4 py-6 sm:px-8 lg:px-10">
        <header class="mb-6 space-y-4">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-stone-500">Gestio interna</p>
                    <h2 class="text-3xl font-bold text-stone-900">{{ $heading ?? 'Dashboard' }}</h2>
                </div>

                <div class="panel flex items-center justify-between gap-4 px-5 py-4 sm:justify-start">
                    <div>
                        <p class="text-sm font-semibold">{{ auth()->user()->full_name }}</p>
                        <p class="text-xs uppercase tracking-[0.25em] text-stone-500">{{ auth()->user()->role->value }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn-secondary" type="submit">Sortir</button>
                    </form>
                </div>
            </div>

            <div class="panel px-3 py-3">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="rounded-2xl bg-stone-950 px-4 py-3 text-stone-100 lg:shrink-0">
                        <p class="text-xs uppercase tracking-[0.35em] text-amber-400">Club Basquet</p>
                        <p class="mt-1 text-sm font-bold">Gestio de Partits</p>
                    </div>

                    <nav class="grid w-full grid-cols-2 gap-2 sm:grid-cols-3 lg:flex lg:flex-1 lg:flex-wrap lg:justify-end">
                        @foreach($items as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                class="inline-flex min-w-0 items-center justify-center gap-2 rounded-2xl px-3 py-3 text-sm font-medium lg:flex-none {{ request()->routeIs($item['route']) ? 'bg-amber-400 text-stone-950' : 'bg-stone-100 text-stone-700 hover:bg-stone-200 hover:text-stone-950' }}"
                            >
                                <span class="material-icons-outlined shrink-0 text-base">{{ $item['icon'] }}</span>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </header>

        @if(session('status'))
            <div class="panel mb-6 border-l-4 border-l-emerald-500 px-5 py-4 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="panel mb-6 border-l-4 border-l-red-500 px-5 py-4 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>
</div>
</body>
</html>
